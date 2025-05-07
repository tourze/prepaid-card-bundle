<?php

namespace PrepaidCardBundle\Service;

use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Symfony\AopDoctrineBundle\Attribute\Transactional;
use Tourze\Symfony\AopLockBundle\Attribute\Lockable;

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
     */
    public function hasEnoughBalance(UserInterface $user, float $cost): bool
    {
        $cost = abs($cost);

        $c = $this->createValidQueryBuilder($user)
            ->select('SUM(a.balance)')
            ->getQuery()
            ->getSingleScalarResult();

        return $c >= $cost;
    }

    /**
     * 预付卡扣费
     */
    #[Transactional]
    public function costPay(UserInterface $user, float $costValue, string $orderId): ?Contract
    {
        $costValue = abs($costValue);
        if (0 === $costValue) {
            throw new \InvalidArgumentException('Cost cannot be zero.');
        }

        if (!$this->hasEnoughBalance($user, $costValue)) {
            $this->logger->error('预付卡金额不足', [
                'userId' => $user->getId(),
                'costValue' => $costValue,
                'orderId' => $orderId,
            ]);

            return null;
        }

        // 创建一个预付订单
        $contract = new Contract();
        // 一般来讲，一个订单只会有一笔预付单
        $contract->setCode($orderId);
        $contract->setCostAmount($costValue);
        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        $myCards = $this->createValidQueryBuilder($user)
            ->addOrderBy('a.expireTime', Criteria::ASC)
            ->addOrderBy('a.id', Criteria::ASC)
            ->getQuery()
            ->getResult();
        foreach ($myCards as $card) {
            if ($costValue <= 0) {
                break;
            }

            /** @var Card $card */
            if ($costValue > $card->getBalance()) {
                $v = $card->getBalance();
                $card->setBalance(0);
                $card->setStatus(PrepaidCardStatus::EMPTY);
            } else {
                $v = $costValue;
                $card->setBalance($card->getBalance() - $costValue);
            }
            $card->checkStatus();

            $costValue = $costValue - $v;

            // 记录消费情况
            $consumption = new Consumption();
            $consumption->setCard($card);
            $consumption->setTitle("订单: {$orderId}");
            $consumption->setOrderId($orderId);
            // 数据库记录的总是负数
            $consumption->setAmount(-$v);
            $consumption->setRefundableAmount($v);
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
        // 默认退所有金额
        if (!$refundAmount) {
            $refundAmount = $contract->getCostAmount();
        }

        $realBack = 0;
        foreach ($contract->getConsumptions() as $consumption) {
            if (!$refundAmount) {
                continue;
            }
            if ($consumption->getRefundableAmount() <= 0) {
                continue;
            }

            // 计算退款金额
            $v = min($consumption->getRefundableAmount(), $refundAmount);
            $realBack += $v;
            $refundAmount = $refundAmount - $v;
            $consumption->setRefundableAmount($consumption->getRefundableAmount() - $v);
            $this->entityManager->persist($consumption);

            // 卡上的余额要变化
            $card = $consumption->getCard();
            $card->setBalance($card->getBalance() + $v);
            $card->checkStatus();
            $this->entityManager->persist($card);

            // 退换需要额外增加一条记录
            $backLog = new Consumption();
            $backLog->setCard($card);
            $backLog->setTitle("{$consumption->getTitle()} - 退还");
            $backLog->setOrderId($consumption->getOrderId());
            $backLog->setAmount($v);
            $backLog->setRefundableAmount(0); // 这种退换的，没得继续退的喔
            $backLog->setContract($contract);
            $this->entityManager->persist($backLog);
        }

        // 记录退款时间
        $contract->setRefundTime(Carbon::now());

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
            ->setParameter('status', PrepaidCardStatus::VALID);
    }
}
