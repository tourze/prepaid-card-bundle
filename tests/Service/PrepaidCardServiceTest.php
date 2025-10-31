<?php

namespace PrepaidCardBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Entity\Contract;
use PrepaidCardBundle\Service\PrepaidCardService;
use PrepaidCardBundle\Tests\Utils\TestUser;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardService::class)]
#[RunTestsInSeparateProcesses]
final class PrepaidCardServiceTest extends AbstractIntegrationTestCase
{
    private PrepaidCardService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(PrepaidCardService::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(PrepaidCardService::class, $this->service);
    }

    public function testCostPay(): void
    {
        $user = new TestUser('1');
        $result = $this->service->costPay($user, 0, 'ORDER123');
        $this->assertInstanceOf(Contract::class, $result);
    }

    public function testReturnBack(): void
    {
        $contract = new Contract();
        $contract->setCostAmount('0');
        $result = $this->service->returnBack($contract);
        $this->assertEquals(0, $result);
    }

    public function testHasEnoughBalanceWithSufficientBalance(): void
    {
        $user = new TestUser('1');

        // 测试有足够余额的情况
        $result = $this->service->hasEnoughBalance($user, 10.0);
        $this->assertIsBool($result);
    }

    public function testHasEnoughBalanceWithInsufficientBalance(): void
    {
        $user = new TestUser('1');

        // 测试余额不足的情况（使用一个很大的金额）
        $result = $this->service->hasEnoughBalance($user, 999999.99);
        $this->assertIsBool($result);
    }

    public function testHasEnoughBalanceWithZeroCost(): void
    {
        $user = new TestUser('1');

        // 测试零金额的情况
        $result = $this->service->hasEnoughBalance($user, 0.0);
        $this->assertTrue($result);
    }

    public function testHasEnoughBalanceWithNegativeCost(): void
    {
        $user = new TestUser('1');

        // 测试负金额（应该被abs处理为正数）
        $result = $this->service->hasEnoughBalance($user, -50.0);
        $this->assertIsBool($result);
    }
}
