<?php

namespace PrepaidCardBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use PrepaidCardBundle\Service\PrepaidCardService;
use Psr\Log\LoggerInterface;

class PrepaidCardServiceReturnBackTest extends TestCase
{
    private MockObject|CardRepository $cardRepository;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|LoggerInterface $logger;
    private PrepaidCardService $service;

    protected function setUp(): void
    {
        $this->cardRepository = $this->createMock(CardRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new PrepaidCardService(
            $this->cardRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testReturnBack_withFullRefund(): void
    {
        // 创建卡片和消费记录
        $card = new Card();
        $card->setBalance('50.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('50');

        $consumption = new Consumption();
        $consumption->setCard($card);
        $consumption->setAmount('-50');
        $consumption->setRefundableAmount('50');
        $consumption->setTitle('订单: ORDER123');
        $consumption->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption);

        // 期望entityManager方法调用 - PHPUnit 10兼容的方式
        $this->entityManager->expects($this->exactly(4))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Consumption::class, $entity),
                    2 => $this->assertInstanceOf(Card::class, $entity),
                    3 => $this->assertInstanceOf(Consumption::class, $entity),
                    4 => $this->assertInstanceOf(Contract::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $refundAmount = $this->service->returnBack($contract);

        // 断言
        $this->assertEquals(50, $refundAmount);
        $this->assertEquals(0, $consumption->getRefundableAmount());
        $this->assertEquals('100.00', $card->getBalance()); // 原50 + 退50
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }

    public function testReturnBack_withPartialRefund(): void
    {
        // 创建卡片和消费记录
        $card = new Card();
        $card->setBalance('50.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('50');

        $consumption = new Consumption();
        $consumption->setCard($card);
        $consumption->setAmount('-50');
        $consumption->setRefundableAmount('50');
        $consumption->setTitle('订单: ORDER123');
        $consumption->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption);

        // 期望entityManager方法调用 - PHPUnit 10兼容的方式
        $this->entityManager->expects($this->exactly(4))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Consumption::class, $entity),
                    2 => $this->assertInstanceOf(Card::class, $entity),
                    3 => $this->assertInstanceOf(Consumption::class, $entity),
                    4 => $this->assertInstanceOf(Contract::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试 - 只退30
        $refundAmount = $this->service->returnBack($contract, 30);

        // 断言
        $this->assertEquals(30, $refundAmount);
        $this->assertEquals(20, $consumption->getRefundableAmount()); // 原50 - 退30
        $this->assertEquals('80.00', $card->getBalance()); // 原50 + 退30
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }

    public function testReturnBack_withZeroRefundableAmount(): void
    {
        // 创建卡片和消费记录
        $card = new Card();
        $card->setBalance('50.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('50');

        $consumption = new Consumption();
        $consumption->setCard($card);
        $consumption->setAmount('-50');
        $consumption->setRefundableAmount('0'); // 已无可退金额
        $consumption->setTitle('订单: ORDER123');
        $consumption->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption);

        // 期望entityManager方法调用 - 只会设置退款时间，不会更新卡和消费
        $this->entityManager->expects($this->exactly(1))
            ->method('persist')
            ->with($this->isInstanceOf(Contract::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $refundAmount = $this->service->returnBack($contract);

        // 断言
        $this->assertEquals(0, $refundAmount);
        $this->assertEquals(0, $consumption->getRefundableAmount());
        $this->assertEquals('50.00', $card->getBalance()); // 余额不变
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }

    public function testReturnBack_withMultipleConsumptions(): void
    {
        // 创建卡片和消费记录
        $card1 = new Card();
        $card1->setBalance('50.00');
        $card1->setStatus(PrepaidCardStatus::VALID);
        $card1->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $card2 = new Card();
        $card2->setBalance('50.00');
        $card2->setStatus(PrepaidCardStatus::VALID);
        $card2->setExpireTime(new \DateTimeImmutable('+2 years')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('60');

        $consumption1 = new Consumption();
        $consumption1->setCard($card1);
        $consumption1->setAmount('-40');
        $consumption1->setRefundableAmount('40');
        $consumption1->setTitle('订单: ORDER123');
        $consumption1->setOrderId('ORDER123');

        $consumption2 = new Consumption();
        $consumption2->setCard($card2);
        $consumption2->setAmount('-20');
        $consumption2->setRefundableAmount('20');
        $consumption2->setTitle('订单: ORDER123');
        $consumption2->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption1);
        $contract->addConsumption($consumption2);

        // 期望entityManager方法调用 - PHPUnit 10兼容的方式
        $this->entityManager->expects($this->exactly(7))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                // 断言各个persist调用的参数类型
                match ($callCount) {
                    1 => $this->assertInstanceOf(Consumption::class, $entity),
                    2 => $this->assertInstanceOf(Card::class, $entity),
                    3 => $this->assertInstanceOf(Consumption::class, $entity),
                    4 => $this->assertInstanceOf(Consumption::class, $entity),
                    5 => $this->assertInstanceOf(Card::class, $entity),
                    6 => $this->assertInstanceOf(Consumption::class, $entity),
                    7 => $this->assertInstanceOf(Contract::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试
        $refundAmount = $this->service->returnBack($contract);

        // 断言
        $this->assertEquals(60, $refundAmount);
        $this->assertEquals(0, $consumption1->getRefundableAmount());
        $this->assertEquals(0, $consumption2->getRefundableAmount());
        $this->assertEquals('90.00', $card1->getBalance()); // 原50 + 退40
        $this->assertEquals('70.00', $card2->getBalance()); // 原50 + 退20
        $this->assertEquals(PrepaidCardStatus::VALID, $card1->getStatus());
        $this->assertEquals(PrepaidCardStatus::VALID, $card2->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }

    public function testReturnBack_withSpecificRefundAmount(): void
    {
        // 创建卡片和消费记录
        $card1 = new Card();
        $card1->setBalance('50.00');
        $card1->setStatus(PrepaidCardStatus::VALID);
        $card1->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $card2 = new Card();
        $card2->setBalance('50.00');
        $card2->setStatus(PrepaidCardStatus::VALID);
        $card2->setExpireTime(new \DateTimeImmutable('+2 years')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('60');

        $consumption1 = new Consumption();
        $consumption1->setCard($card1);
        $consumption1->setAmount('-40');
        $consumption1->setRefundableAmount('40');
        $consumption1->setTitle('订单: ORDER123');
        $consumption1->setOrderId('ORDER123');

        $consumption2 = new Consumption();
        $consumption2->setCard($card2);
        $consumption2->setAmount('-20');
        $consumption2->setRefundableAmount('20');
        $consumption2->setTitle('订单: ORDER123');
        $consumption2->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption1);
        $contract->addConsumption($consumption2);

        // 期望entityManager方法调用 - PHPUnit 10兼容的方式
        $this->entityManager->expects($this->exactly(7))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                // 断言各个persist调用的参数类型
                match ($callCount) {
                    1 => $this->assertInstanceOf(Consumption::class, $entity),
                    2 => $this->assertInstanceOf(Card::class, $entity),
                    3 => $this->assertInstanceOf(Consumption::class, $entity),
                    4 => $this->assertInstanceOf(Consumption::class, $entity),
                    5 => $this->assertInstanceOf(Card::class, $entity),
                    6 => $this->assertInstanceOf(Consumption::class, $entity),
                    7 => $this->assertInstanceOf(Contract::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试 - 指定退款金额为30，应该从两张卡上按比例退
        $refundAmount = $this->service->returnBack($contract, 30);

        // 断言
        $this->assertEquals(30, $refundAmount);
        $this->assertEquals(20, $consumption1->getRefundableAmount()); // 原40 - 退20
        $this->assertEquals(10, $consumption2->getRefundableAmount()); // 原20 - 退10
        $this->assertEquals('70.00', $card1->getBalance()); // 原50 + 退20
        $this->assertEquals('60.00', $card2->getBalance()); // 原50 + 退10
        $this->assertEquals(PrepaidCardStatus::VALID, $card1->getStatus());
        $this->assertEquals(PrepaidCardStatus::VALID, $card2->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }

    public function testReturnBack_withLargerRefundThanCostAmount(): void
    {
        // 创建卡片和消费记录
        $card = new Card();
        $card->setBalance('50.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $contract = new Contract();
        $contract->setCostAmount('50');

        $consumption = new Consumption();
        $consumption->setCard($card);
        $consumption->setAmount('-50');
        $consumption->setRefundableAmount('50');
        $consumption->setTitle('订单: ORDER123');
        $consumption->setOrderId('ORDER123');

        // 设置合约的消费记录
        $contract->addConsumption($consumption);

        // 期望entityManager方法调用 - PHPUnit 10兼容的方式
        $this->entityManager->expects($this->exactly(4))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Consumption::class, $entity),
                    2 => $this->assertInstanceOf(Card::class, $entity),
                    3 => $this->assertInstanceOf(Consumption::class, $entity),
                    4 => $this->assertInstanceOf(Contract::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        // 执行测试 - 尝试退100，但最多只能退50
        $refundAmount = $this->service->returnBack($contract, 100);

        // 断言
        $this->assertEquals(50, $refundAmount); // 只能退50
        $this->assertEquals(0, $consumption->getRefundableAmount());
        $this->assertEquals('100.00', $card->getBalance()); // 原50 + 退50
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $contract->getRefundTime());
    }
}
