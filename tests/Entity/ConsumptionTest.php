<?php

namespace PrepaidCardBundle\Tests\Entity;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;

class ConsumptionTest extends TestCase
{
    private Consumption $consumption;

    protected function setUp(): void
    {
        $this->consumption = new Consumption();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->consumption->setTitle('测试消费');
        $this->assertEquals('测试消费', $this->consumption->getTitle());

        $this->consumption->setOrderId('ORDER123456');
        $this->assertEquals('ORDER123456', $this->consumption->getOrderId());

        $this->consumption->setAmount('100.50');
        $this->assertEquals('100.50', $this->consumption->getAmount());

        $this->consumption->setRefundableAmount('50.25');
        $this->assertEquals('50.25', $this->consumption->getRefundableAmount());

        $this->consumption->setCreatedFromIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->consumption->getCreatedFromIp());

        $this->consumption->setCreatedBy('user123');
        $this->assertEquals('user123', $this->consumption->getCreatedBy());

        $createTime = new \DateTime();
        $this->consumption->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->consumption->getCreateTime());
    }

    public function testCardRelationship(): void
    {
        /** @var Card&MockObject $card */
        $card = $this->createMock(Card::class);
        $this->consumption->setCard($card);
        $this->assertSame($card, $this->consumption->getCard());
    }

    public function testContractRelationship(): void
    {
        /** @var Contract&MockObject $contract */
        $contract = $this->createMock(Contract::class);
        $this->consumption->setContract($contract);
        $this->assertSame($contract, $this->consumption->getContract());

        // 测试设置为null
        $this->consumption->setContract(null);
        $this->assertNull($this->consumption->getContract());
    }

    public function testToString(): void
    {
        // 测试没有ID时
        $consumption = new Consumption();
        $this->assertEquals('', (string) $consumption);

        // 设置标题和金额后
        $this->consumption->setTitle('测试消费');
        $this->consumption->setAmount('100.00');
        
        // 由于没有实际的ID，toString会返回空字符串
        // 这里我们主要测试方法不会抛出异常
        $result = (string) $this->consumption;
        $this->assertIsString($result);
    }

    public function testRetrieveApiArray(): void
    {
        // 设置测试数据
        $this->consumption->setTitle('API测试消费');
        $this->consumption->setOrderId('API_ORDER_123');
        $this->consumption->setAmount('-50.00');

        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->consumption->setCreateTime($createTime);

        // 模拟Contract
        /** @var Contract&MockObject $contract */
        $contract = $this->createMock(Contract::class);
        $contract->expects($this->once())
            ->method('getCostAmount')
            ->willReturn('100.00');
        $this->consumption->setContract($contract);

        $array = $this->consumption->retrieveApiArray();

        $this->assertIsArray($array);
        $this->assertEquals('API测试消费', $array['title']);
        $this->assertEquals('API_ORDER_123', $array['orderId']);
        $this->assertEquals('-50.00', $array['cost']);
        $this->assertEquals('100.00', $array['contract']);
        $this->assertEquals('2024-01-01 10:00:00', $array['createTime']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testRetrieveAdminArray(): void
    {
        // 设置测试数据
        $this->consumption->setTitle('管理员测试消费');
        $this->consumption->setOrderId('ADMIN_ORDER_123');
        $this->consumption->setAmount('-75.50');
        $this->consumption->setRefundableAmount('25.00');
        $this->consumption->setCreatedFromIp('192.168.1.100');

        $createTime = new \DateTime('2024-01-01 15:30:45');
        $this->consumption->setCreateTime($createTime);

        $array = $this->consumption->retrieveAdminArray();

        $this->assertIsArray($array);
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
        $consumption = new Consumption();
        
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
        // 测试可为null的字段
        $this->consumption->setOrderId(null);
        $this->assertNull($this->consumption->getOrderId());

        $this->consumption->setRefundableAmount(null);
        $this->assertNull($this->consumption->getRefundableAmount());

        $this->consumption->setCreatedFromIp(null);
        $this->assertNull($this->consumption->getCreatedFromIp());

        $this->consumption->setCreatedBy(null);
        $this->assertNull($this->consumption->getCreatedBy());

        $this->consumption->setCreateTime(null);
        $this->assertNull($this->consumption->getCreateTime());
    }

    public function testAmountHandling(): void
    {
        // 测试金额处理
        $this->consumption->setAmount('100.50');
        $this->assertEquals('100.50', $this->consumption->getAmount());

        // 测试负金额（退款）
        $this->consumption->setAmount('-50.25');
        $this->assertEquals('-50.25', $this->consumption->getAmount());

        // 测试零金额
        $this->consumption->setAmount('0.00');
        $this->assertEquals('0.00', $this->consumption->getAmount());
    }

    public function testRefundableAmountHandling(): void
    {
        // 测试可退款金额处理
        $this->consumption->setRefundableAmount('100.50');
        $this->assertEquals('100.50', $this->consumption->getRefundableAmount());

        // 测试零可退款金额
        $this->consumption->setRefundableAmount('0.00');
        $this->assertEquals('0.00', $this->consumption->getRefundableAmount());
    }

    public function testIpAddressHandling(): void
    {
        // 测试IPv4地址
        $this->consumption->setCreatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->consumption->getCreatedFromIp());

        // 测试IPv6地址
        $this->consumption->setCreatedFromIp('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $this->consumption->getCreatedFromIp());

        // 测试本地IP
        $this->consumption->setCreatedFromIp('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->consumption->getCreatedFromIp());
    }

    public function testTimeHandling(): void
    {
        // 测试时间处理
        $createTime = new \DateTime('2024-01-01 12:30:45');
        $this->consumption->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->consumption->getCreateTime());

        // 测试null时间
        $this->consumption->setCreateTime(null);
        $this->assertNull($this->consumption->getCreateTime());
    }

    public function testOrderIdFormat(): void
    {
        // 测试不同格式的订单ID
        $orderIds = [
            'ORDER123456',
            'ORD-2024-001',
            '20240101001',
            'TEST_ORDER_123',
            'order.2024.jan.001'
        ];

        foreach ($orderIds as $orderId) {
            $this->consumption->setOrderId($orderId);
            $this->assertEquals($orderId, $this->consumption->getOrderId());
        }
    }
} 