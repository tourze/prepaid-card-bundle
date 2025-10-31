<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Contract::class)]
final class ContractTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Contract();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'code' => ['code', 'CONTRACT123'],
            'costAmount' => ['costAmount', '150.75'],
            'refundTime' => ['refundTime', new \DateTimeImmutable('2024-06-15 14:30:00')],
            'createdFromIp' => ['createdFromIp', '192.168.1.50'],
            'createdBy' => ['createdBy', 'user456'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
        ];
    }

    public function testConsumptionsCollectionAdd(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $contract->getConsumptions());
        $this->assertCount(0, $contract->getConsumptions());

        // 添加消费记录 - 使用真实的Consumption实例
        $consumption1 = new Consumption();
        $consumption1->setTitle('测试消费');
        $consumption1->setOrderId('TXN_001');
        $consumption1->setAmount('50.00');

        $contract->addConsumption($consumption1);
        $this->assertCount(1, $contract->getConsumptions());
        $this->assertTrue($contract->getConsumptions()->contains($consumption1));
        // 验证双向关联已正确设置
        $this->assertSame($contract, $consumption1->getContract());

        // 重复添加同一个消费记录不应增加数量
        $contract->addConsumption($consumption1);
        $this->assertCount(1, $contract->getConsumptions());
    }

    public function testConsumptionsCollectionRemove(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);

        // 使用真实的Consumption实例
        $consumption1 = new Consumption();
        $consumption1->setTitle('测试消费');
        $consumption1->setOrderId('TXN_001');
        $consumption1->setAmount('50.00');

        $contract->addConsumption($consumption1);
        // 验证添加成功
        $this->assertCount(1, $contract->getConsumptions());
        $this->assertSame($contract, $consumption1->getContract());

        // 移除消费记录
        $contract->removeConsumption($consumption1);
        $this->assertCount(0, $contract->getConsumptions());
        $this->assertFalse($contract->getConsumptions()->contains($consumption1));
        // 验证双向关联已正确清除
        $this->assertNull($consumption1->getContract());
    }

    public function testGetRefundableAmountWithNoConsumptions(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);

        // 没有消费记录时，可退款金额应为0
        $refundableAmount = $contract->getRefundableAmount();
        $this->assertEquals(0.0, $refundableAmount);
    }

    public function testGetRefundableAmountWithSingleConsumption(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);

        // 使用真实的Consumption实例并设置退款金额
        $consumption = new Consumption();
        $consumption->setTitle('测试消费');
        $consumption->setOrderId('TXN_001');
        $consumption->setAmount('100.00');
        $consumption->setRefundableAmount('50.25');

        $contract->addConsumption($consumption);

        $refundableAmount = $contract->getRefundableAmount();
        $this->assertEquals(50.25, $refundableAmount);
    }

    public function testGetRefundableAmountWithMultipleConsumptions(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);

        /*
         * 使用匿名类实现 Consumption 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. 需要模拟 getRefundableAmount() 业务方法返回不同金额
         * 3. 测试多个消费记录的汇总计算逻辑
         * 4. 匿名类提供可控的返回值，便于测试聚合计算
         */
        $consumption1 = new class extends Consumption {
            public function getRefundableAmount(): string
            {
                return '30.50';
            }

            public function setContract(?Contract $contract): void
            {
                // 匿名类实现，仅用于测试
            }
        };

        $consumption2 = new class extends Consumption {
            public function getRefundableAmount(): string
            {
                return '25.75';
            }

            public function setContract(?Contract $contract): void
            {
                // 匿名类实现，仅用于测试
            }
        };

        $consumption3 = new class extends Consumption {
            public function getRefundableAmount(): string
            {
                return '10.00';
            }

            public function setContract(?Contract $contract): void
            {
                // 匿名类实现，仅用于测试
            }
        };

        $contract->addConsumption($consumption1);
        $contract->addConsumption($consumption2);
        $contract->addConsumption($consumption3);

        $refundableAmount = $contract->getRefundableAmount();
        $this->assertEquals(66.25, $refundableAmount);
    }

    public function testToString(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);
        $contract->setCode('CONTRACT123');

        // 测试默认ID为0的情况
        $this->assertEquals('0', (string) $contract);

        // 使用反射设置ID来测试有ID的情况
        $reflection = new \ReflectionClass($contract);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($contract, 123);

        $this->assertEquals('123', (string) $contract);
    }

    public function testRetrieveAdminArray(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);
        $contract->setCode('CONTRACT123');
        $contract->setCostAmount('150.75');

        $array = $contract->retrieveAdminArray();
        $this->assertEquals('CONTRACT123', $array['code']);
        $this->assertEquals('150.75', $array['costAmount']);
    }

    public function testRetrieveApiArray(): void
    {
        $contract = $this->createEntity();
        self::assertInstanceOf(Contract::class, $contract);
        $contract->setCode('CONTRACT123');
        $contract->setCostAmount('150.75');

        $array = $contract->retrieveApiArray();
        $this->assertEquals('CONTRACT123', $array['code']);
        $this->assertEquals('150.75', $array['costAmount']);
    }
}
