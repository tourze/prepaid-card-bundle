<?php

namespace PrepaidCardBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Repository\CardRepository;
use PrepaidCardBundle\Service\PrepaidCardService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PrepaidCardServiceBalanceTest extends TestCase
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

    private function createTestUser(): UserInterface
    {
        return new class('1', 'test_user') implements UserInterface {
            private string $id;
            private string $username;

            public function __construct(string $id, string $username)
            {
                $this->id = $id;
                $this->username = $username;
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
                // 空实现
            }

            public function getUserIdentifier(): string
            {
                return $this->username;
            }
        };
    }

    public function testHasEnoughBalance_withSufficientBalance(): void
    {
        // 创建mock用户
        $user = $this->createTestUser();

        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为
        $this->cardRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(1))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 假设总余额为100
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('100.00');

        // 测试消费50单位
        $result = $this->service->hasEnoughBalance($user, 50);

        // 断言余额足够
        $this->assertTrue($result);
    }

    public function testHasEnoughBalance_withInsufficientBalance(): void
    {
        // 创建mock用户
        $user = $this->createTestUser();

        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为
        $this->cardRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(1))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 假设总余额为30
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('30.00');

        // 测试消费50单位
        $result = $this->service->hasEnoughBalance($user, 50);

        // 断言余额不足
        $this->assertFalse($result);
    }

    public function testHasEnoughBalance_withZeroBalance(): void
    {
        // 创建mock用户
        $user = $this->createTestUser();

        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为
        $this->cardRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(1))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 假设总余额为0
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('0.00');

        // 测试消费50单位
        $result = $this->service->hasEnoughBalance($user, 50);

        // 断言余额不足
        $this->assertFalse($result);
    }

    public function testHasEnoughBalance_withZeroCost(): void
    {
        // 创建mock用户
        $user = $this->createTestUser();

        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为
        $this->cardRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(1))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 假设总余额为10
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('10.00');

        // 测试消费0单位
        $result = $this->service->hasEnoughBalance($user, 0);

        // 断言余额足够
        $this->assertTrue($result);
    }

    public function testHasEnoughBalance_withNegativeCost(): void
    {
        // 创建mock用户
        $user = $this->createTestUser();

        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为
        $this->cardRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(1))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 假设总余额为10
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('10.00');

        // 测试消费负数单位
        $result = $this->service->hasEnoughBalance($user, -10);

        // 断言余额足够（因为负数消费实际上是增加余额）
        $this->assertTrue($result);
    }
}
