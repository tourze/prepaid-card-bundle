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
final class PrepaidCardServiceReturnBackTest extends AbstractIntegrationTestCase
{
    private PrepaidCardService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(PrepaidCardService::class);
    }

    public function testReturnBackWithZeroAmount(): void
    {
        $contract = new Contract();
        $contract->setCostAmount('0');
        $result = $this->service->returnBack($contract);
        $this->assertEquals(0, $result);
    }

    public function testReturnBackWithValidContract(): void
    {
        $contract = new Contract();
        $contract->setCostAmount('10.00');
        $result = $this->service->returnBack($contract);
        $this->assertIsFloat($result);
    }

    public function testCostPay(): void
    {
        $user = new TestUser('1');
        // 测试零金额的costPay（应该返回Contract）
        $result = $this->service->costPay($user, 0, 'RETURN_BACK_TEST_ORDER');
        $this->assertInstanceOf(Contract::class, $result);
        $this->assertNotNull($result->getCode());
    }
}
