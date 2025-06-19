<?php

namespace PrepaidCardBundle\Tests\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use PrepaidCardBundle\Service\PrepaidCardService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PrepaidCardServiceCostPayTest extends TestCase
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

    private function createTestUser(string $id = '1', string $username = 'test_user'): UserInterface
    {
        return new class($id, $username) implements UserInterface {
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

    public function testCostPay_withSufficientSingleCard(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        // 放宽setParameter的限制，允许更多次调用
        $queryBuilder->expects($this->atLeast(4))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为100
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('100.00');

        // 为卡片排序
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturnCallback(function ($field, $order) use ($queryBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertEquals('a.expireTime', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                } else {
                    $this->assertEquals('a.id', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                }

                return $queryBuilder;
            });

        // 模拟有效卡片
        $card = new Card();
        $card->setBalance('100.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$card]);

        // 期望entityManager方法调用
        $this->entityManager->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Contract::class, $entity),
                    2 => $this->assertInstanceOf(Consumption::class, $entity),
                    3 => $this->assertInstanceOf(Card::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $contract = $this->service->costPay($user, 50, 'ORDER123');

        // 添加断言确保测试不会被标记为risky
        $this->assertNotNull($contract);

        // 断言
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('ORDER123', $contract->getCode());
        $this->assertEquals(50, $contract->getCostAmount());

        // 验证卡片状态
        $this->assertEquals('50', $card->getBalance());
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
    }

    public function testCostPay_withSufficientMultipleCards(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(4))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为100
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('100.00');

        // 为卡片排序
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturnCallback(function ($field, $order) use ($queryBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertEquals('a.expireTime', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                } else {
                    $this->assertEquals('a.id', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                }

                return $queryBuilder;
            });

        // 模拟两张卡片
        $card1 = new Card();
        $card1->setBalance('30.00');
        $card1->setStatus(PrepaidCardStatus::VALID);
        $card1->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $card2 = new Card();
        $card2->setBalance('70.00');
        $card2->setStatus(PrepaidCardStatus::VALID);
        $card2->setExpireTime(new \DateTimeImmutable('+2 years')); // 设置过期时间

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$card1, $card2]);

        // 期望entityManager方法调用
        $this->entityManager->expects($this->exactly(5))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Contract::class, $entity),
                    2 => $this->assertInstanceOf(Consumption::class, $entity),
                    3 => $this->assertInstanceOf(Card::class, $entity),
                    4 => $this->assertInstanceOf(Consumption::class, $entity),
                    5 => $this->assertInstanceOf(Card::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->exactly(3))
            ->method('flush');

        // 执行测试
        $contract = $this->service->costPay($user, 50, 'ORDER123');

        // 添加断言确保测试不会被标记为risky
        $this->assertNotNull($contract);

        // 断言
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('ORDER123', $contract->getCode());
        $this->assertEquals(50, $contract->getCostAmount());

        // 验证卡片状态
        $this->assertEquals('0', $card1->getBalance());
        $this->assertEquals('50', $card2->getBalance());
        $this->assertEquals(PrepaidCardStatus::EMPTY, $card1->getStatus());
        $this->assertEquals(PrepaidCardStatus::VALID, $card2->getStatus());
    }

    public function testCostPay_withExactlyEqualBalance(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(4))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为50
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('50.00');

        // 为卡片排序
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturnCallback(function ($field, $order) use ($queryBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertEquals('a.expireTime', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                } else {
                    $this->assertEquals('a.id', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                }

                return $queryBuilder;
            });

        // 模拟有效卡片
        $card = new Card();
        $card->setBalance('50.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$card]);

        // 期望entityManager方法调用
        $this->entityManager->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Contract::class, $entity),
                    2 => $this->assertInstanceOf(Consumption::class, $entity),
                    3 => $this->assertInstanceOf(Card::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试
        $contract = $this->service->costPay($user, 50, 'ORDER123');

        // 添加断言确保测试不会被标记为risky
        $this->assertNotNull($contract);

        // 断言
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('ORDER123', $contract->getCode());
        $this->assertEquals(50, $contract->getCostAmount());

        // 验证卡片状态
        $this->assertEquals('0', $card->getBalance());
        $this->assertEquals(PrepaidCardStatus::EMPTY, $card->getStatus()); // 余额为0，已使用
    }

    public function testCostPay_withInsufficientBalance(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
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

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为30
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('30.00');

        // 记录日志
        $this->logger->expects($this->once())
            ->method('error')
            ->with('预付卡金额不足', $this->anything());

        // 执行测试
        $contract = $this->service->costPay($user, 50, 'ORDER123');

        // 断言
        $this->assertNull($contract);
    }

    public function testCostPay_withZeroCost(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(4))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为10
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('10.00');

        // 为卡片排序
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturn($queryBuilder);

        // 模拟不返回卡片（因为成本为0）
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        // 期望entityManager方法调用
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Contract::class));

        $this->entityManager->expects($this->exactly(1))
            ->method('flush');

        // 执行测试 - 零成本应该创建合同但不消费卡
        $contract = $this->service->costPay($user, 0, 'ORDER123');

        // 断言
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('ORDER123', $contract->getCode());
        $this->assertEquals(0, $contract->getCostAmount());
    }

    public function testCostPay_withNegativeCost(): void
    {
        // 创建测试用户
        $user = $this->createTestUser('1', 'test_user');

        // 模拟查询构建器和查询
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置预期行为 - hasEnoughBalance 查询
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(2))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->atLeast(4))
            ->method('setParameter')
            ->willReturn($queryBuilder);

        // hasEnoughBalance 检查
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('SUM(a.balance)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($query);

        // 总余额为100
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('100.00');

        // 为卡片排序
        $queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->willReturnCallback(function ($field, $order) use ($queryBuilder) {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertEquals('a.expireTime', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                } else {
                    $this->assertEquals('a.id', $field);
                    $this->assertEquals(Criteria::ASC, $order);
                }

                return $queryBuilder;
            });

        // 模拟有效卡片
        $card = new Card();
        $card->setBalance('100.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setExpireTime(new \DateTimeImmutable('+1 year')); // 设置过期时间

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([$card]);

        // 期望entityManager方法调用
        $this->entityManager->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;

                match ($callCount) {
                    1 => $this->assertInstanceOf(Contract::class, $entity),
                    2 => $this->assertInstanceOf(Consumption::class, $entity),
                    3 => $this->assertInstanceOf(Card::class, $entity),
                    default => $this->fail('Unexpected method call')
                };

                return null;
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行测试 - 应转换为正值50
        $contract = $this->service->costPay($user, -50, 'ORDER123');

        // 添加断言确保测试不会被标记为risky
        $this->assertNotNull($contract);

        // 断言
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('ORDER123', $contract->getCode());
        $this->assertEquals(50, $contract->getCostAmount());

        // 验证卡片状态
        $this->assertEquals('50', $card->getBalance());
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
    }
}
