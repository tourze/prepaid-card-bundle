<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Contract;

class ContractTest extends TestCase
{
    private Contract $contract;

    protected function setUp(): void
    {
        $this->contract = new Contract();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->contract->setCode('CONTRACT123');
        $this->assertEquals('CONTRACT123', $this->contract->getCode());

        $this->contract->setCostAmount('150.75');
        $this->assertEquals('150.75', $this->contract->getCostAmount());

        $refundTime = new \DateTime('2024-06-15 14:30:00');
        $this->contract->setRefundTime($refundTime);
        $this->assertEquals($refundTime, $this->contract->getRefundTime());

        $this->contract->setCreatedFromIp('192.168.1.50');
        $this->assertEquals('192.168.1.50', $this->contract->getCreatedFromIp());

        $this->contract->setCreatedBy('user456');
        $this->assertEquals('user456', $this->contract->getCreatedBy());

        $createTime = new \DateTime();
        $this->contract->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->contract->getCreateTime());
    }

    public function testConsumptionsCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->contract->getConsumptions());
        $this->assertCount(0, $this->contract->getConsumptions());

        // 添加消费记录
        /** @var Consumption&MockObject $consumption1 */
        $consumption1 = $this->createMock(Consumption::class);
        $consumption1->expects($this->once())->method('setContract')->with($this->contract);
        
        $this->contract->addConsumption($consumption1);
        $this->assertCount(1, $this->contract->getConsumptions());
        $this->assertTrue($this->contract->getConsumptions()->contains($consumption1));

        // 重复添加同一个消费记录不应增加数量
        $this->contract->addConsumption($consumption1);
        $this->assertCount(1, $this->contract->getConsumptions());
    }

    public function testConsumptionsCollectionRemove(): void
    {
        /** @var Consumption&MockObject $consumption1 */
        $consumption1 = $this->createMock(Consumption::class);
        $consumption1->expects($this->exactly(2))->method('setContract'); // 第一次设置为contract，第二次设置为null
        
        $this->contract->addConsumption($consumption1);

        // 移除消费记录
        $consumption1->expects($this->once())->method('getContract')->willReturn($this->contract);
        
        $this->contract->removeConsumption($consumption1);
        $this->assertCount(0, $this->contract->getConsumptions());
        $this->assertFalse($this->contract->getConsumptions()->contains($consumption1));
    }

    public function testGetRefundableAmountWithNoConsumptions(): void
    {
        // 没有消费记录时，可退款金额应为0
        $refundableAmount = $this->contract->getRefundableAmount();
        $this->assertEquals(0.0, $refundableAmount);
    }

    public function testGetRefundableAmountWithSingleConsumption(): void
    {
        /** @var Consumption&MockObject $consumption */
        $consumption = $this->createMock(Consumption::class);
        $consumption->expects($this->once())->method('getRefundableAmount')->willReturn('50.25');
        $consumption->method('setContract');
        
        $this->contract->addConsumption($consumption);
        
        $refundableAmount = $this->contract->getRefundableAmount();
        $this->assertEquals(50.25, $refundableAmount);
    }

    public function testGetRefundableAmountWithMultipleConsumptions(): void
    {
        /** @var Consumption&MockObject $consumption1 */
        $consumption1 = $this->createMock(Consumption::class);
        $consumption1->expects($this->once())->method('getRefundableAmount')->willReturn('30.50');
        $consumption1->method('setContract');

        /** @var Consumption&MockObject $consumption2 */
        $consumption2 = $this->createMock(Consumption::class);
        $consumption2->expects($this->once())->method('getRefundableAmount')->willReturn('25.75');
        $consumption2->method('setContract');

        /** @var Consumption&MockObject $consumption3 */
        $consumption3 = $this->createMock(Consumption::class);
        $consumption3->expects($this->once())->method('getRefundableAmount')->willReturn('10.00');
        $consumption3->method('setContract');
        
        $this->contract->addConsumption($consumption1);
        $this->contract->addConsumption($consumption2);
        $this->contract->addConsumption($consumption3);
        
        $refundableAmount = $this->contract->getRefundableAmount();
        $this->assertEquals(66.25, $refundableAmount); // 30.50 + 25.75 + 10.00
    }

    public function testGetRefundableAmountWithZeroValues(): void
    {
        /** @var Consumption&MockObject $consumption1 */
        $consumption1 = $this->createMock(Consumption::class);
        $consumption1->expects($this->once())->method('getRefundableAmount')->willReturn('0.00');
        $consumption1->method('setContract');

        /** @var Consumption&MockObject $consumption2 */
        $consumption2 = $this->createMock(Consumption::class);
        $consumption2->expects($this->once())->method('getRefundableAmount')->willReturn('50.00');
        $consumption2->method('setContract');
        
        $this->contract->addConsumption($consumption1);
        $this->contract->addConsumption($consumption2);
        
        $refundableAmount = $this->contract->getRefundableAmount();
        $this->assertEquals(50.0, $refundableAmount);
    }

    public function testRetrieveApiArray(): void
    {
        $this->contract->setCode('API_CONTRACT_001');
        $this->contract->setCostAmount('299.99');

        $array = $this->contract->retrieveApiArray();
        $this->assertEquals('API_CONTRACT_001', $array['code']);
        $this->assertEquals('299.99', $array['costAmount']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testRetrieveAdminArray(): void
    {
        $this->contract->setCode('ADMIN_CONTRACT_001');
        $this->contract->setCostAmount('199.50');
        $this->contract->setCreatedFromIp('10.0.0.1');

        $refundTime = new \DateTime('2024-06-01 16:45:30');
        $this->contract->setRefundTime($refundTime);

        $createTime = new \DateTime('2024-05-01 10:15:20');
        $this->contract->setCreateTime($createTime);

        $array = $this->contract->retrieveAdminArray();
        $this->assertEquals('ADMIN_CONTRACT_001', $array['code']);
        $this->assertEquals('199.50', $array['costAmount']);
        $this->assertEquals('2024-06-01 16:45:30', $array['refundTime']);
        $this->assertEquals('2024-05-01 10:15:20', $array['createTime']);
        $this->assertEquals('10.0.0.1', $array['createdFromIp']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testDefaultValues(): void
    {
        $contract = new Contract();
        
        // 默认值测试
        $this->assertEquals(0, $contract->getId());
        $this->assertNull($contract->getCode());
        $this->assertNull($contract->getRefundTime());
        $this->assertNull($contract->getCreatedFromIp());
        $this->assertNull($contract->getCreatedBy());
        $this->assertNull($contract->getCreateTime());
        $this->assertInstanceOf(Collection::class, $contract->getConsumptions());
        $this->assertCount(0, $contract->getConsumptions());
    }

    public function testNullableFields(): void
    {
        // 测试可为null的字段
        $this->contract->setRefundTime(null);
        $this->assertNull($this->contract->getRefundTime());

        $this->contract->setCreatedFromIp(null);
        $this->assertNull($this->contract->getCreatedFromIp());

        $this->contract->setCreatedBy(null);
        $this->assertNull($this->contract->getCreatedBy());

        $this->contract->setCreateTime(null);
        $this->assertNull($this->contract->getCreateTime());
    }

    public function testCostAmountHandling(): void
    {
        // 测试不同格式的费用金额
        $amounts = [
            '100.00',
            '999.99',
            '0.01',
            '50.5', // 单位小数
            '1000' // 整数
        ];

        foreach ($amounts as $amount) {
            $this->contract->setCostAmount($amount);
            $this->assertEquals($amount, $this->contract->getCostAmount());
        }
    }

    public function testTimeHandling(): void
    {
        // 测试时间处理
        $refundTime = new \DateTime('2024-06-15 09:30:45');
        $createTime = new \DateTime('2024-05-01 14:20:10');

        $this->contract->setRefundTime($refundTime);
        $this->contract->setCreateTime($createTime);

        $this->assertEquals($refundTime, $this->contract->getRefundTime());
        $this->assertEquals($createTime, $this->contract->getCreateTime());

        // 测试null时间
        $this->contract->setRefundTime(null);
        $this->contract->setCreateTime(null);
        
        $this->assertNull($this->contract->getRefundTime());
        $this->assertNull($this->contract->getCreateTime());
    }

    public function testIpAddressHandling(): void
    {
        // 测试不同格式的IP地址
        $ipAddresses = [
            '127.0.0.1',
            '192.168.1.100',
            '10.0.0.1',
            '203.208.60.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334' // IPv6
        ];

        foreach ($ipAddresses as $ip) {
            $this->contract->setCreatedFromIp($ip);
            $this->assertEquals($ip, $this->contract->getCreatedFromIp());
        }
    }

    public function testCodeUniqueness(): void
    {
        // 测试编码的唯一性设置
        $codes = [
            'CONTRACT001',
            'CT-2024-001',
            'PREPAID_20240601_001',
            '20240601CT001'
        ];

        foreach ($codes as $code) {
            $this->contract->setCode($code);
            $this->assertEquals($code, $this->contract->getCode());
        }
    }
} 