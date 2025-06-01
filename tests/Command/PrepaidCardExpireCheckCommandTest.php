<?php

namespace PrepaidCardBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Command\PrepaidCardExpireCheckCommand;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use ReflectionMethod;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepaidCardExpireCheckCommandTest extends TestCase
{
    /** @var CardRepository&MockObject */
    private $cardRepository;
    
    /** @var EntityManagerInterface&MockObject */
    private $entityManager;
    
    private PrepaidCardExpireCheckCommand $command;

    protected function setUp(): void
    {
        $this->cardRepository = $this->createMock(CardRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->command = new PrepaidCardExpireCheckCommand(
            $this->cardRepository,
            $this->entityManager
        );
    }

    private function executeCommand(): int
    {
        $reflection = new ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        
        /** @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /** @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        
        return $reflection->invoke($this->command, $input, $output);
    }

    public function testGetName(): void
    {
        $this->assertEquals('prepaid-card:expire-check', PrepaidCardExpireCheckCommand::NAME);
    }

    public function testExecuteWithExpiredCards(): void
    {
        // 准备过期卡片数据
        /** @var Card&MockObject $expiredCard1 */
        $expiredCard1 = $this->createMock(Card::class);
        $expiredCard1->expects($this->once())->method('setStatus')->with(PrepaidCardStatus::EXPIRED);

        /** @var Card&MockObject $expiredCard2 */
        $expiredCard2 = $this->createMock(Card::class);
        $expiredCard2->expects($this->once())->method('setStatus')->with(PrepaidCardStatus::EXPIRED);

        $expiredCards = [$expiredCard1, $expiredCard2];

        // 为过期检查设置QueryBuilder链
        /** @var Query&MockObject $expiredQuery */
        $expiredQuery = $this->createMock(Query::class);
        $expiredQuery->expects($this->once())->method('getResult')->willReturn($expiredCards);

        /** @var QueryBuilder&MockObject $expiredQb */
        $expiredQb = $this->createMock(QueryBuilder::class);
        $expiredQb->expects($this->once())->method('where')->with('a.status=:status')->willReturnSelf();
        $expiredQb->expects($this->exactly(2))->method('andWhere')->willReturnSelf();
        $expiredQb->expects($this->exactly(2))->method('setParameter')->willReturnSelf();
        $expiredQb->expects($this->once())->method('setMaxResults')->with(500)->willReturnSelf();
        $expiredQb->expects($this->once())->method('getQuery')->willReturn($expiredQuery);

        // 为余额为空检查设置QueryBuilder链
        /** @var Query&MockObject $emptyQuery */
        $emptyQuery = $this->createMock(Query::class);
        $emptyQuery->expects($this->once())->method('getResult')->willReturn([]);

        /** @var QueryBuilder&MockObject $emptyQb */
        $emptyQb = $this->createMock(QueryBuilder::class);
        $emptyQb->expects($this->once())->method('where')->with('a.balance<=0 AND a.status=:status')->willReturnSelf();
        $emptyQb->expects($this->once())->method('setParameter')->with('status', PrepaidCardStatus::VALID)->willReturnSelf();
        $emptyQb->expects($this->once())->method('setMaxResults')->with(500)->willReturnSelf();
        $emptyQb->expects($this->once())->method('getQuery')->willReturn($emptyQuery);

        // 配置repository返回不同的QueryBuilder
        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturnOnConsecutiveCalls($expiredQb, $emptyQb);

        // 验证persist和flush调用
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $result = $this->executeCommand();
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithEmptyBalanceCards(): void
    {
        // 准备余额为空的卡片数据
        /** @var Card&MockObject $emptyCard1 */
        $emptyCard1 = $this->createMock(Card::class);
        $emptyCard1->expects($this->once())->method('setStatus')->with(PrepaidCardStatus::EMPTY);

        $emptyCards = [$emptyCard1];

        // 为过期检查设置QueryBuilder链（无结果）
        /** @var Query&MockObject $expiredQuery */
        $expiredQuery = $this->createMock(Query::class);
        $expiredQuery->expects($this->once())->method('getResult')->willReturn([]);

        /** @var QueryBuilder&MockObject $expiredQb */
        $expiredQb = $this->createMock(QueryBuilder::class);
        $expiredQb->expects($this->once())->method('where')->willReturnSelf();
        $expiredQb->expects($this->exactly(2))->method('andWhere')->willReturnSelf();
        $expiredQb->expects($this->exactly(2))->method('setParameter')->willReturnSelf();
        $expiredQb->expects($this->once())->method('setMaxResults')->willReturnSelf();
        $expiredQb->expects($this->once())->method('getQuery')->willReturn($expiredQuery);

        // 为余额为空检查设置QueryBuilder链
        /** @var Query&MockObject $emptyQuery */
        $emptyQuery = $this->createMock(Query::class);
        $emptyQuery->expects($this->once())->method('getResult')->willReturn($emptyCards);

        /** @var QueryBuilder&MockObject $emptyQb */
        $emptyQb = $this->createMock(QueryBuilder::class);
        $emptyQb->expects($this->once())->method('where')->willReturnSelf();
        $emptyQb->expects($this->once())->method('setParameter')->willReturnSelf();
        $emptyQb->expects($this->once())->method('setMaxResults')->willReturnSelf();
        $emptyQb->expects($this->once())->method('getQuery')->willReturn($emptyQuery);

        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($expiredQb, $emptyQb);

        // 验证persist和flush调用
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $result = $this->executeCommand();
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithNoCardsToUpdate(): void
    {
        // 两个查询都返回空结果
        /** @var Query&MockObject $emptyQuery1 */
        $emptyQuery1 = $this->createMock(Query::class);
        $emptyQuery1->expects($this->once())->method('getResult')->willReturn([]);

        /** @var Query&MockObject $emptyQuery2 */
        $emptyQuery2 = $this->createMock(Query::class);
        $emptyQuery2->expects($this->once())->method('getResult')->willReturn([]);

        /** @var QueryBuilder&MockObject $qb1 */
        $qb1 = $this->createMock(QueryBuilder::class);
        $qb1->method('where')->willReturnSelf();
        $qb1->method('andWhere')->willReturnSelf();
        $qb1->method('setParameter')->willReturnSelf();
        $qb1->method('setMaxResults')->willReturnSelf();
        $qb1->method('getQuery')->willReturn($emptyQuery1);

        /** @var QueryBuilder&MockObject $qb2 */
        $qb2 = $this->createMock(QueryBuilder::class);
        $qb2->method('where')->willReturnSelf();
        $qb2->method('setParameter')->willReturnSelf();
        $qb2->method('setMaxResults')->willReturnSelf();
        $qb2->method('getQuery')->willReturn($emptyQuery2);

        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($qb1, $qb2);

        // 没有卡片需要更新，不应调用persist
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $result = $this->executeCommand();
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testExecuteWithBothExpiredAndEmptyCards(): void
    {
        // 准备两种类型的卡片
        /** @var Card&MockObject $expiredCard */
        $expiredCard = $this->createMock(Card::class);
        $expiredCard->expects($this->once())->method('setStatus')->with(PrepaidCardStatus::EXPIRED);

        /** @var Card&MockObject $emptyCard */
        $emptyCard = $this->createMock(Card::class);
        $emptyCard->expects($this->once())->method('setStatus')->with(PrepaidCardStatus::EMPTY);

        // 为过期检查设置
        /** @var Query&MockObject $expiredQuery */
        $expiredQuery = $this->createMock(Query::class);
        $expiredQuery->expects($this->once())->method('getResult')->willReturn([$expiredCard]);

        /** @var QueryBuilder&MockObject $expiredQb */
        $expiredQb = $this->createMock(QueryBuilder::class);
        $expiredQb->method('where')->willReturnSelf();
        $expiredQb->method('andWhere')->willReturnSelf();
        $expiredQb->method('setParameter')->willReturnSelf();
        $expiredQb->method('setMaxResults')->willReturnSelf();
        $expiredQb->method('getQuery')->willReturn($expiredQuery);

        // 为余额为空检查设置
        /** @var Query&MockObject $emptyQuery */
        $emptyQuery = $this->createMock(Query::class);
        $emptyQuery->expects($this->once())->method('getResult')->willReturn([$emptyCard]);

        /** @var QueryBuilder&MockObject $emptyQb */
        $emptyQb = $this->createMock(QueryBuilder::class);
        $emptyQb->method('where')->willReturnSelf();
        $emptyQb->method('setParameter')->willReturnSelf();
        $emptyQb->method('setMaxResults')->willReturnSelf();
        $emptyQb->method('getQuery')->willReturn($emptyQuery);

        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($expiredQb, $emptyQb);

        // 验证两次persist和flush调用
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->exactly(2))->method('flush');

        $result = $this->executeCommand();
        $this->assertEquals(Command::SUCCESS, $result);
    }

    public function testQueryParametersAndLimits(): void
    {
        // 验证查询参数的正确设置
        /** @var Query&MockObject $query1 */
        $query1 = $this->createMock(Query::class);
        $query1->method('getResult')->willReturn([]);

        /** @var Query&MockObject $query2 */
        $query2 = $this->createMock(Query::class);
        $query2->method('getResult')->willReturn([]);

        /** @var QueryBuilder&MockObject $qb1 */
        $qb1 = $this->createMock(QueryBuilder::class);
        $qb1->expects($this->once())->method('where')->with('a.status=:status')->willReturnSelf();
        $qb1->expects($this->exactly(2))->method('andWhere')->willReturnSelf();
        $qb1->expects($this->exactly(2))->method('setParameter')->willReturnSelf();
        $qb1->expects($this->once())->method('setMaxResults')->with(500)->willReturnSelf();
        $qb1->method('getQuery')->willReturn($query1);

        /** @var QueryBuilder&MockObject $qb2 */
        $qb2 = $this->createMock(QueryBuilder::class);
        $qb2->expects($this->once())->method('where')->with('a.balance<=0 AND a.status=:status')->willReturnSelf();
        $qb2->expects($this->once())->method('setParameter')->with('status', PrepaidCardStatus::VALID)->willReturnSelf();
        $qb2->expects($this->once())->method('setMaxResults')->with(500)->willReturnSelf();
        $qb2->method('getQuery')->willReturn($query2);

        $this->cardRepository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturnOnConsecutiveCalls($qb1, $qb2);

        $this->entityManager->method('flush');

        $result = $this->executeCommand();
        $this->assertEquals(Command::SUCCESS, $result);
    }
} 