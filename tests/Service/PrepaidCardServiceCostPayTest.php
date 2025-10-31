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
final class PrepaidCardServiceCostPayTest extends AbstractIntegrationTestCase
{
    private PrepaidCardService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(PrepaidCardService::class);
    }

    public function testCostPayWithZeroCost(): void
    {
        $user = new TestUser('1');
        $result = $this->service->costPay($user, 0, 'ORDER123');
        $this->assertInstanceOf(Contract::class, $result);
    }

    public function testCostPayWithValidUser(): void
    {
        $user = new TestUser('1');
        $result = $this->service->costPay($user, 0, 'ORDER456');
        $this->assertInstanceOf(Contract::class, $result);
        $this->assertNotNull($result->getCode());
    }

    public function testReturnBack(): void
    {
        $user = new TestUser('1');
        // 先创建一个Contract用于退款测试
        $contract = $this->service->costPay($user, 0, 'COST_PAY_RETURN_TEST');
        $this->assertInstanceOf(Contract::class, $contract);

        // 测试退款
        $result = $this->service->returnBack($contract);
        $this->assertIsNumeric($result);
    }
}
