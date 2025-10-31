<?php

namespace PrepaidCardBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Consumption::class)]
final class ConsumptionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Consumption();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'title' => ['title', '测试消费'],
            'orderId' => ['orderId', 'ORDER123456'],
            'amount' => ['amount', '100.50'],
            'refundableAmount' => ['refundableAmount', '50.25'],
            'createdFromIp' => ['createdFromIp', '127.0.0.1'],
            'createdBy' => ['createdBy', 'user123'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
        ];
    }

    public function testCardRelationship(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 使用真实的Card实例
        $card = new Card();
        $card->setCardNumber('TEST_CARD_001');
        $card->setParValue('100.00');

        $consumption->setCard($card);
        $this->assertSame($card, $consumption->getCard());
    }

    public function testContractRelationship(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 使用真实的Contract实例
        $contract = new Contract();
        $contract->setCode('CONTRACT_001');
        $contract->setCostAmount('1000.00');

        $consumption->setContract($contract);
        $this->assertSame($contract, $consumption->getContract());

        // 测试设置为null
        $consumption->setContract(null);
        $this->assertNull($consumption->getContract());
    }

    public function testToString(): void
    {
        // 测试没有ID时
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);
        $this->assertEquals('', (string) $consumption);

        // 设置标题和金额后
        $consumption2 = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption2);
        $consumption2->setTitle('测试消费');
        $consumption2->setAmount('100.00');

        // 由于ID为0（默认值），toString应该返回空字符串
        $this->assertEquals('', (string) $consumption2);
    }

    public function testRetrieveApiArray(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 设置测试数据
        $consumption->setTitle('API测试消费');
        $consumption->setOrderId('API_ORDER_123');
        $consumption->setAmount('-50.00');

        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $consumption->setCreateTime($createTime);

        // 使用匿名类实现Contract，遵循symplify.noTestMocks规则
        $contract = new class extends Contract {
            public function getCostAmount(): string
            {
                return '100.00';
            }
        };
        $consumption->setContract($contract);

        $array = $consumption->retrieveApiArray();
        $this->assertEquals('API测试消费', $array['title']);
        $this->assertEquals('API_ORDER_123', $array['orderId']);
        $this->assertEquals('-50.00', $array['cost']);
        $this->assertEquals('100.00', $array['contract']);
        $this->assertEquals('2024-01-01 10:00:00', $array['createTime']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testRetrieveAdminArray(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 设置测试数据
        $consumption->setTitle('管理员测试消费');
        $consumption->setOrderId('ADMIN_ORDER_123');
        $consumption->setAmount('-75.50');
        $consumption->setRefundableAmount('25.00');
        $consumption->setCreatedFromIp('192.168.1.100');

        $createTime = new \DateTimeImmutable('2024-01-01 15:30:45');
        $consumption->setCreateTime($createTime);

        $array = $consumption->retrieveAdminArray();
        $this->assertEquals('管理员测试消费', $array['title']);
        $this->assertEquals('ADMIN_ORDER_123', $array['orderId']);
        $this->assertEquals('-75.50', $array['amount']);
        $this->assertEquals('25.00', $array['refundableAmount']);
        $this->assertEquals('192.168.1.100', $array['createdFromIp']);
        $this->assertEquals('2024-01-01 15:30:45', $array['createTime']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testDefaultValues(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 默认值测试
        $this->assertEquals(0, $consumption->getId());
        $this->assertNull($consumption->getOrderId());
        $this->assertNull($consumption->getRefundableAmount());
        $this->assertNull($consumption->getCreatedFromIp());
        $this->assertNull($consumption->getCreatedBy());
        $this->assertNull($consumption->getCreateTime());
        $this->assertNull($consumption->getContract());
    }

    public function testNullableFields(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试可为null的字段
        $consumption->setOrderId(null);
        $this->assertNull($consumption->getOrderId());

        $consumption->setRefundableAmount(null);
        $this->assertNull($consumption->getRefundableAmount());

        $consumption->setCreatedFromIp(null);
        $this->assertNull($consumption->getCreatedFromIp());

        $consumption->setCreatedBy(null);
        $this->assertNull($consumption->getCreatedBy());

        $consumption->setCreateTime(null);
        $this->assertNull($consumption->getCreateTime());
    }

    public function testAmountHandling(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试金额处理
        $consumption->setAmount('100.50');
        $this->assertEquals('100.50', $consumption->getAmount());

        // 测试负金额（退款）
        $consumption->setAmount('-50.25');
        $this->assertEquals('-50.25', $consumption->getAmount());

        // 测试零金额
        $consumption->setAmount('0.00');
        $this->assertEquals('0.00', $consumption->getAmount());
    }

    public function testRefundableAmountHandling(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试可退款金额处理
        $consumption->setRefundableAmount('100.50');
        $this->assertEquals('100.50', $consumption->getRefundableAmount());

        // 测试零可退款金额
        $consumption->setRefundableAmount('0.00');
        $this->assertEquals('0.00', $consumption->getRefundableAmount());
    }

    public function testIpAddressHandling(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试IPv4地址
        $consumption->setCreatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $consumption->getCreatedFromIp());

        // 测试IPv6地址
        $consumption->setCreatedFromIp('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $consumption->getCreatedFromIp());

        // 测试本地IP
        $consumption->setCreatedFromIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $consumption->getCreatedFromIp());
    }

    public function testTimeHandling(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试时间处理
        $createTime = new \DateTimeImmutable('2024-01-01 12:30:45');
        $consumption->setCreateTime($createTime);
        $this->assertEquals($createTime, $consumption->getCreateTime());

        // 测试null时间
        $consumption->setCreateTime(null);
        $this->assertNull($consumption->getCreateTime());
    }

    public function testOrderIdFormat(): void
    {
        $consumption = $this->createEntity();
        self::assertInstanceOf(Consumption::class, $consumption);

        // 测试不同格式的订单ID
        $orderIds = [
            'ORDER123456',
            'ORD-2024-001',
            '20240101001',
            'TEST_ORDER_123',
            'order.2024.jan.001',
        ];

        foreach ($orderIds as $orderId) {
            $consumption->setOrderId($orderId);
            $this->assertEquals($orderId, $consumption->getOrderId());
        }
    }
}
