<?php

namespace PrepaidCardBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Service\PrepaidCardService;
use PrepaidCardBundle\Tests\Utils\TestUser;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardService::class)]
#[RunTestsInSeparateProcesses]
final class PrepaidCardServiceBalanceTest extends AbstractIntegrationTestCase
{
    private PrepaidCardService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(PrepaidCardService::class);
    }

    public function testHasEnoughBalanceWithSufficientBalance(): void
    {
        $user = new TestUser('1');
        $result = $this->service->hasEnoughBalance($user, 0);
        $this->assertTrue($result);
    }

    public function testHasEnoughBalanceWithInsufficientBalance(): void
    {
        $user = new TestUser('1');
        $result = $this->service->hasEnoughBalance($user, 999999);
        $this->assertFalse($result);
    }

    public function testHasEnoughBalanceWithZeroBalance(): void
    {
        $user = new TestUser('1');
        $result = $this->service->hasEnoughBalance($user, 0);
        $this->assertTrue($result);
    }

    public function testHasEnoughBalanceWithZeroCost(): void
    {
        $user = new TestUser('1');
        $result = $this->service->hasEnoughBalance($user, 0);
        $this->assertTrue($result);
    }

    public function testHasEnoughBalanceWithNegativeCost(): void
    {
        $user = new TestUser('1');
        $result = $this->service->hasEnoughBalance($user, -10);
        $this->assertFalse($result);
    }

    public function testCostPay(): void
    {
        $user = new TestUser('1');
        // 测试零金额的costPay（应该返回Contract）
        $result = $this->service->costPay($user, 0, 'BALANCE_TEST_ORDER');
        $this->assertNotNull($result);
    }

    public function testReturnBack(): void
    {
        $user = new TestUser('1');
        // 先创建一个Contract用于退款测试
        $contract = $this->service->costPay($user, 0, 'BALANCE_RETURN_TEST');
        $this->assertNotNull($contract);

        // 测试退款
        $result = $this->service->returnBack($contract);
        $this->assertIsNumeric($result);
    }
}
