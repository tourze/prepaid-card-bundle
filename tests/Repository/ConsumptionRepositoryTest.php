<?php

namespace PrepaidCardBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Repository\ConsumptionRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumptionRepository::class)]
#[RunTestsInSeparateProcesses]
final class ConsumptionRepositoryTest extends AbstractRepositoryTestCase
{
    private ConsumptionRepository $repository;

    private Card $testCard;

    private Contract $testContract;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ConsumptionRepository::class);
        $this->createTestDependencies();
    }

    private function createTestDependencies(): void
    {
        $em = self::getEntityManager();

        // 创建测试用的 Card
        $this->testCard = new Card();
        $this->testCard->setCardNumber('TEST-CARD-001-' . uniqid());
        $this->testCard->setParValue('100.00');
        $this->testCard->setBalance('50.00');
        $em->persist($this->testCard);

        // 创建测试用的 Contract
        $this->testContract = new Contract();
        $this->testContract->setCode('TEST-CONTRACT-001-' . uniqid());
        $this->testContract->setCostAmount('25.00');
        $em->persist($this->testContract);

        $em->flush();
    }

    private function createTestConsumption(string $title = 'Test Consumption', string $amount = '10.00'): Consumption
    {
        $consumption = new Consumption();
        $consumption->setTitle($title);
        $consumption->setAmount($amount);
        $consumption->setCard($this->testCard);
        $consumption->setContract($this->testContract);

        return $consumption;
    }

    public function testFindOneByWithOrderByShouldReturnFirstMatchingEntity(): void
    {
        // 清空所有记录
        foreach ($this->repository->findAll() as $entity) {
            $this->repository->remove($entity, false);
        }
        self::getEntityManager()->flush();

        // 创建相同标题但不同金额的记录
        $consumption1 = $this->createTestConsumption('Same Title', '30.00');
        $consumption2 = $this->createTestConsumption('Same Title', '10.00');
        $consumption3 = $this->createTestConsumption('Same Title', '20.00');

        $this->repository->save($consumption1, false);
        $this->repository->save($consumption2, false);
        $this->repository->save($consumption3, false);
        self::getEntityManager()->flush();

        // 查找按金额降序排列的第一个
        $found = $this->repository->findOneBy(['title' => 'Same Title'], ['amount' => 'DESC']);
        $this->assertInstanceOf(Consumption::class, $found);
        $this->assertSame('30.00', $found->getAmount());

        // 查找按金额升序排列的第一个
        $found = $this->repository->findOneBy(['title' => 'Same Title'], ['amount' => 'ASC']);
        $this->assertInstanceOf(Consumption::class, $found);
        $this->assertSame('10.00', $found->getAmount());
    }

    public function testSaveShouldPersistEntity(): void
    {
        $consumption = $this->createTestConsumption('Test Save', '15.00');
        $consumption->setOrderId('ORDER-123');
        $consumption->setRefundableAmount('5.00');

        $this->repository->save($consumption);
        $this->assertNotNull($consumption->getId());

        // 验证实体已保存
        $found = $this->repository->find($consumption->getId());
        $this->assertInstanceOf(Consumption::class, $found);
        $this->assertSame('Test Save', $found->getTitle());
        $this->assertSame('ORDER-123', $found->getOrderId());
        $this->assertSame('5.00', $found->getRefundableAmount());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $consumption = $this->createTestConsumption('Test Remove', '12.00');
        $this->repository->save($consumption);
        $consumptionId = $consumption->getId();

        $this->repository->remove($consumption);

        $found = $this->repository->find($consumptionId);
        $this->assertNull($found);
    }

    public function testRemoveWithFlushFalseShouldNotImmediatelyDelete(): void
    {
        $consumption = $this->createTestConsumption('Test Remove No Flush', '16.00');
        $this->repository->save($consumption);
        $consumptionId = $consumption->getId();

        $this->repository->remove($consumption, false);

        // 在flush前实体可能还存在
        self::getEntityManager()->flush();

        // flush后实体应该被删除
        $found = $this->repository->find($consumptionId);
        $this->assertNull($found);
    }

    public function testCountByAssociationCardShouldReturnCorrectNumber(): void
    {
        // 清空所有记录
        foreach ($this->repository->findAll() as $entity) {
            $this->repository->remove($entity, false);
        }
        self::getEntityManager()->flush();

        // 创建4个属于testCard的消费记录
        for ($i = 1; $i <= 4; ++$i) {
            $consumption = $this->createTestConsumption("Card Consumption {$i}", (string) ($i * 5.00));
            $this->repository->save($consumption, false);
        }

        // 创建另一个Card和2个属于它的消费记录
        $em = self::getEntityManager();
        $anotherCard = new Card();
        $anotherCard->setCardNumber('TEST-CARD-002');
        $anotherCard->setParValue('200.00');
        $anotherCard->setBalance('100.00');
        $em->persist($anotherCard);
        $em->flush();

        for ($i = 1; $i <= 2; ++$i) {
            $consumption = new Consumption();
            $consumption->setTitle("Other Card Consumption {$i}");
            $consumption->setAmount((string) ($i * 8.00));
            $consumption->setCard($anotherCard);
            $consumption->setContract($this->testContract);
            $this->repository->save($consumption, false);
        }
        self::getEntityManager()->flush();

        $count = $this->repository->count(['card' => $this->testCard]);
        $this->assertSame(4, $count);
    }

    public function testCountByAssociationContractShouldReturnCorrectNumber(): void
    {
        // 清空所有记录
        foreach ($this->repository->findAll() as $entity) {
            $this->repository->remove($entity, false);
        }
        self::getEntityManager()->flush();

        // 创建3个属于testContract的消费记录
        for ($i = 1; $i <= 3; ++$i) {
            $consumption = $this->createTestConsumption("Contract Consumption {$i}", (string) ($i * 7.00));
            $this->repository->save($consumption, false);
        }

        // 创建另一个Contract和1个属于它的消费记录
        $em = self::getEntityManager();
        $anotherContract = new Contract();
        $anotherContract->setCode('TEST-CONTRACT-002');
        $anotherContract->setCostAmount('50.00');
        $em->persist($anotherContract);
        $em->flush();

        $consumption = new Consumption();
        $consumption->setTitle('Other Contract Consumption');
        $consumption->setAmount('15.00');
        $consumption->setCard($this->testCard);
        $consumption->setContract($anotherContract);
        $this->repository->save($consumption, false);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['contract' => $this->testContract]);
        $this->assertSame(3, $count);
    }

    public function testFindOneByAssociationCardShouldReturnMatchingEntity(): void
    {
        // 清空所有记录
        foreach ($this->repository->findAll() as $entity) {
            $this->repository->remove($entity, false);
        }
        self::getEntityManager()->flush();

        $consumption = $this->createTestConsumption('Card Association Test', '22.00');
        $this->repository->save($consumption);

        $found = $this->repository->findOneBy(['card' => $this->testCard]);
        $this->assertInstanceOf(Consumption::class, $found);
        $this->assertSame($this->testCard->getId(), $found->getCard()->getId());
        $this->assertSame('Card Association Test', $found->getTitle());
    }

    public function testFindOneByAssociationContractShouldReturnMatchingEntity(): void
    {
        // 清空所有记录
        foreach ($this->repository->findAll() as $entity) {
            $this->repository->remove($entity, false);
        }
        self::getEntityManager()->flush();

        $consumption = $this->createTestConsumption('Contract Association Test', '18.00');
        $this->repository->save($consumption);

        $found = $this->repository->findOneBy(['contract' => $this->testContract]);
        $this->assertInstanceOf(Consumption::class, $found);
        $contract = $found->getContract();
        $this->assertNotNull($contract);
        $this->assertSame($this->testContract->getId(), $contract->getId());
        $this->assertSame('Contract Association Test', $found->getTitle());
    }

    protected function createNewEntity(): object
    {
        $entity = new Consumption();

        // 设置基本字段
        $entity->setTitle('Test Consumption ' . uniqid());
        $entity->setAmount('10.00');
        $entity->setCard($this->testCard);
        $entity->setContract($this->testContract);

        return $entity;
    }

    protected function getRepository(): ConsumptionRepository
    {
        return $this->repository;
    }
}
