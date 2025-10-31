<?php

namespace PrepaidCardBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Monolog\Attribute\WithMonologChannel;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Symfony\AopDoctrineBundle\Attribute\Transactional;
use Tourze\Symfony\AopLockBundle\Attribute\Lockable;

#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'prepaid_card')]
class PrepaidCardService
{
    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 是否有足够的余额
     *
     * 注意：不考虑并发，该方法仅用于余额查询
     */
    public function hasEnoughBalance(UserInterface $user, float $cost): bool
    {
        $cost = abs($cost);

        $c = $this->createValidQueryBuilder($user)
            ->select('SUM(a.balance)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $c >= $cost;
    }

    /**
     * 预付卡扣费
     */
    #[Transactional]
    public function costPay(UserInterface $user, float $costValue, string $orderId): ?Contract
    {
        $costValue = abs($costValue);

        if (!$this->hasEnoughBalance($user, $costValue)) {
            $this->logger->error('预付卡金额不足', [
                'userId' => $user->getUserIdentifier(),
                'costValue' => $costValue,
                'orderId' => $orderId,
            ]);

            return null;
        }

        // 创建一个预付订单
        $contract = new Contract();
        // 一般来讲，一个订单只会有一笔预付单
        $contract->setCode($orderId);
        $contract->setCostAmount((string) $costValue);
        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        /** @var Card[] $myCards */
        $myCards = $this->createValidQueryBuilder($user)
            ->addOrderBy('a.expireTime', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        foreach ($myCards as $card) {
            if ($costValue <= 0) {
                break;
            }

            if ($costValue > (float) $card->getBalance()) {
                $v = (float) $card->getBalance();
                $card->setBalance('0');
                $card->setStatus(PrepaidCardStatus::EMPTY);
            } else {
                $v = $costValue;
                $card->setBalance((string) ((float) $card->getBalance() - $costValue));
            }
            $card->checkStatus();

            $costValue -= $v;

            // 记录消费情况
            $consumption = new Consumption();
            $consumption->setCard($card);
            $consumption->setTitle("订单: {$orderId}");
            $consumption->setOrderId($orderId);
            // 数据库记录的总是负数
            $consumption->setAmount((string) (-$v));
            $consumption->setRefundableAmount((string) $v);
            $consumption->setContract($contract);

            $this->entityManager->persist($consumption);
            $this->entityManager->persist($card);
            $this->entityManager->flush();
        }

        return $contract;
    }

    /**
     * 预付卡退款
     *
     * @return int|float 实际可以扣除的金额
     */
    #[Lockable]
    #[Transactional]
    public function returnBack(Contract $contract, ?float $refundAmount = null): float|int
    {
        $refundAmount ??= (float) $contract->getCostAmount();
        $refundableConsumptions = $this->getRefundableConsumptions($contract);

        if ([] === $refundableConsumptions) {
            return $this->recordRefundTime($contract, 0);
        }

        /** @var array<float> $amounts */
        $amounts = array_map(fn ($c) => (float) $c->getRefundableAmount(), $refundableConsumptions);
        $totalRefundable = array_sum($amounts);
        $actualRefundAmount = min($refundAmount, $totalRefundable);

        if ($actualRefundAmount <= 0) {
            return $this->recordRefundTime($contract, 0);
        }

        $realBack = $this->processRefunds($refundableConsumptions, $actualRefundAmount, $totalRefundable);

        return $this->recordRefundTime($contract, $realBack);
    }

    /**
     * @return array<Consumption>
     */
    private function getRefundableConsumptions(Contract $contract): array
    {
        $consumptions = [];
        foreach ($contract->getConsumptions() as $consumption) {
            if ($consumption->getRefundableAmount() > 0) {
                $consumptions[] = $consumption;
            }
        }

        return $consumptions;
    }

    /**
     * @param array<Consumption> $consumptions
     */
    private function processRefunds(array $consumptions, float $actualRefundAmount, float $totalRefundable): float
    {
        $realBack = 0;
        $isSingleConsumption = (1 === count($consumptions));

        foreach ($consumptions as $consumption) {
            $refundAmount = $isSingleConsumption
                ? $actualRefundAmount
                : $this->calculateProportionalRefund($consumption, $actualRefundAmount, $totalRefundable);

            $realBack += $this->processConsumptionRefund($consumption, $refundAmount);
        }

        return $realBack;
    }

    private function calculateProportionalRefund(Consumption $consumption, float $actualRefundAmount, float $totalRefundable): float
    {
        $ratio = (float) $consumption->getRefundableAmount() / $totalRefundable;

        return min((float) $consumption->getRefundableAmount(), round($actualRefundAmount * $ratio, 2));
    }

    private function processConsumptionRefund(Consumption $consumption, float $refundAmount): float
    {
        $v = min((float) $consumption->getRefundableAmount(), $refundAmount);

        $consumption->setRefundableAmount((string) ((float) $consumption->getRefundableAmount() - $v));
        $this->entityManager->persist($consumption);

        $this->updateCardBalance($consumption->getCard(), $v);
        $this->createRefundLog($consumption, $v);

        return $v;
    }

    /**
     * 更新卡余额
     *
     * 注意：不考虑并发，该方法在事务上下文中使用
     */
    private function updateCardBalance(Card $card, float $amount): void
    {
        $card->setBalance(number_format((float) $card->getBalance() + $amount, 2, '.', ''));
        $card->checkStatus();
        $this->entityManager->persist($card);
    }

    private function createRefundLog(Consumption $consumption, float $amount): void
    {
        $backLog = new Consumption();
        $backLog->setCard($consumption->getCard());
        $backLog->setTitle("{$consumption->getTitle()} - 退还");
        $backLog->setOrderId($consumption->getOrderId());
        $backLog->setAmount((string) $amount);
        $backLog->setRefundableAmount('0');
        $backLog->setContract($consumption->getContract());
        $this->entityManager->persist($backLog);
    }

    private function recordRefundTime(Contract $contract, float $realBack): float
    {
        $contract->setRefundTime(CarbonImmutable::now());
        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $realBack;
    }

    private function createValidQueryBuilder(UserInterface $user): QueryBuilder
    {
        return $this->cardRepository->createQueryBuilder('a')
            ->where('a.owner = :owner')
            ->setParameter('owner', $user)
            ->andWhere('a.balance > 0')
            ->andWhere('a.status = :status')
            ->setParameter('status', PrepaidCardStatus::VALID)
        ;
    }
}
