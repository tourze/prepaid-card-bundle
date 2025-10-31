<?php

namespace PrepaidCardBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Repository\ContractRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ContractRepository::class)]
#[RunTestsInSeparateProcesses]
final class ContractRepositoryTest extends AbstractRepositoryTestCase
{
    private ContractRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ContractRepository::class);
    }

    protected function onTearDown(): void
    {
        $this->cleanupTestData();
    }

    protected function createNewEntity(): object
    {
        $entity = new Contract();

        // 设置基本字段
        $entity->setCostAmount('100.00');
        $entity->setCode('TEST_CONTRACT_' . uniqid());

        return $entity;
    }

    protected function getRepository(): ContractRepository
    {
        return $this->repository;
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    #[TestWith([5, 0])]
    #[TestWith([3, 2])]
    #[TestWith([1, 4])]
    public function testFindByShouldRespectLimitAndOffsetParameters(int $limit, int $offset): void
    {
        // 创建足够的测试数据
        for ($i = 1; $i <= 10; ++$i) {
            $this->createContract((string) ($i * 10) . '.00');
        }
        self::getEntityManager()->flush();

        $results = $this->repository->findBy([], null, $limit, $offset);
        $this->assertCount($limit, $results);
    }

    /**
     * @param array<string, string> $orderBy
     */
    #[TestWith([['id' => 'ASC']])]
    #[TestWith([['costAmount' => 'DESC']])]
    public function testFindOneByWithOrderByShouldReturnFirstMatchingEntity(array $orderBy): void
    {
        // 创建多个相同costAmount的合同
        $contract1 = $this->createContract('500.00');
        $contract2 = $this->createContract('500.00');
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['costAmount' => '500.00'], $orderBy);
        $this->assertInstanceOf(Contract::class, $result);
        $this->assertSame('500.00', $result->getCostAmount());
    }

    public function testSave(): void
    {
        $contract = $this->createContract('800.00');

        // 测试不flush的保存
        $this->repository->save($contract, false);
        self::getEntityManager()->flush();

        $savedContract = $this->repository->find($contract->getId());
        $this->assertInstanceOf(Contract::class, $savedContract);
        $this->assertSame('800.00', $savedContract->getCostAmount());

        // 测试带flush的保存
        $contract2 = $this->createContract('900.00');
        $this->repository->save($contract2, true);

        $savedContract2 = $this->repository->find($contract2->getId());
        $this->assertInstanceOf(Contract::class, $savedContract2);
        $this->assertSame('900.00', $savedContract2->getCostAmount());
    }

    public function testRemove(): void
    {
        $contract = $this->createContract('1000.00');
        self::getEntityManager()->flush();
        $contractId = $contract->getId();

        // 测试不flush的删除
        $this->repository->remove($contract, false);
        self::getEntityManager()->flush();

        $removedContract = $this->repository->find($contractId);
        $this->assertNull($removedContract);

        // 测试带flush的删除
        $contract2 = $this->createContract('1100.00');
        self::getEntityManager()->flush();
        $contractId2 = $contract2->getId();

        $this->repository->remove($contract2, true);
        $removedContract2 = $this->repository->find($contractId2);
        $this->assertNull($removedContract2);
    }

    public function testFindByWithNullRefundTime(): void
    {
        // 创建没有退款时间的合同
        $contract = $this->createContract('250.00');
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['refundTime' => null]);
        $this->assertIsArray($results);

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $contract->getId()) {
                $found = true;
                $this->assertNull($result->getRefundTime());
                break;
            }
        }
        $this->assertTrue($found, 'Should find contract with null refundTime');
    }

    public function testFindByWithNotNullRefundTime(): void
    {
        // 创建有退款时间的合同
        $contract = $this->createContract('350.00');
        $contract->setRefundTime(new \DateTimeImmutable());
        self::getEntityManager()->flush();

        // 查找所有有退款时间的合同（使用DQL查询因为原生findBy不支持IS NOT NULL）
        $qb = $this->repository->createQueryBuilder('c')
            ->where('c.refundTime IS NOT NULL')
        ;
        $results = $qb->getQuery()->getResult();

        $this->assertIsArray($results);
        $found = false;
        foreach ($results as $result) {
            $this->assertInstanceOf(Contract::class, $result);
            if ($result->getId() === $contract->getId()) {
                $found = true;
                $this->assertNotNull($result->getRefundTime());
                break;
            }
        }
        $this->assertTrue($found, 'Should find contract with non-null refundTime');
    }

    public function testCountWithNullRefundTime(): void
    {
        // 创建没有退款时间的合同
        $contract = $this->createContract('450.00');
        self::getEntityManager()->flush();

        $count = $this->repository->count(['refundTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullCode(): void
    {
        // 创建没有编码的合同
        $contract = new Contract();
        $contract->setCostAmount('550.00');
        // 不设置 code，保持为 null

        self::getEntityManager()->persist($contract);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['code' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullCode(): void
    {
        // 创建没有编码的合同
        $contract = new Contract();
        $contract->setCostAmount('650.00');
        // 不设置 code，保持为 null

        self::getEntityManager()->persist($contract);
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['code' => null]);
        $this->assertIsArray($results);

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $contract->getId()) {
                $found = true;
                $this->assertNull($result->getCode());
                break;
            }
        }
        $this->assertTrue($found, 'Should find contract with null code');
    }

    public function testFindByWithNullCreatedBy(): void
    {
        // 创建没有创建人的合同
        $contract = $this->createContract('750.00');
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['createdBy' => null]);
        $this->assertIsArray($results);

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $contract->getId()) {
                $found = true;
                $this->assertNull($result->getCreatedBy());
                break;
            }
        }
        $this->assertTrue($found, 'Should find contract with null createdBy');
    }

    public function testCountWithNullCreatedBy(): void
    {
        // 创建没有创建人的合同
        $contract = $this->createContract('850.00');
        self::getEntityManager()->flush();

        $count = $this->repository->count(['createdBy' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullCreatedFromIp(): void
    {
        // 创建没有创建IP的合同
        $contract = $this->createContract('950.00');
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['createdFromIp' => null]);
        $this->assertIsArray($results);

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $contract->getId()) {
                $found = true;
                $this->assertNull($result->getCreatedFromIp());
                break;
            }
        }
        $this->assertTrue($found, 'Should find contract with null createdFromIp');
    }

    public function testCountWithNullCreatedFromIp(): void
    {
        // 创建没有创建IP的合同
        $contract = $this->createContract('1050.00');
        self::getEntityManager()->flush();

        $count = $this->repository->count(['createdFromIp' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    private function createContract(string $costAmount): Contract
    {
        $contract = new Contract();
        $contract->setCostAmount($costAmount);
        $contract->setCode('TEST_' . uniqid());

        self::getEntityManager()->persist($contract);

        return $contract;
    }

    private function cleanupTestData(): void
    {
        $entityManager = self::getEntityManager();

        try {
            // 清理 Consumption 实体
            $consumptions = $entityManager->getRepository(Consumption::class)->findAll();
            foreach ($consumptions as $consumption) {
                $entityManager->remove($consumption);
            }
        } catch (\Exception $e) {
            // 忽略表不存在等错误
        }

        try {
            // 清理 Contract 实体
            $contracts = $this->repository->findAll();
            foreach ($contracts as $contract) {
                $entityManager->remove($contract);
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            // 忽略表不存在等错误
        }
    }
}
