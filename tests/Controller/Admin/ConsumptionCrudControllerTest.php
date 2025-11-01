<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\ConsumptionCrudController;
use PrepaidCardBundle\Entity\Consumption;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumptionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ConsumptionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): ConsumptionCrudController
    {
        return self::getService(ConsumptionCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '标题' => ['标题'];
        yield '礼品卡' => ['礼品卡'];
        yield '关联订单ID' => ['关联订单ID'];
        yield '消费金额' => ['消费金额'];
        yield '关联订单' => ['关联订单'];
        yield '可退款金额' => ['可退款金额'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'card' => ['card'];
        yield 'orderId' => ['orderId'];
        yield 'amount' => ['amount'];
        yield 'contract' => ['contract'];
        yield 'refundableAmount' => ['refundableAmount'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'card' => ['card'];
        yield 'orderId' => ['orderId'];
        yield 'amount' => ['amount'];
        yield 'contract' => ['contract'];
        yield 'refundableAmount' => ['refundableAmount'];
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Consumption CRUD
        $link = $crawler->filter('a[href*="ConsumptionCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateConsumption(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new ConsumptionCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditConsumption(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new ConsumptionCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailConsumption(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new ConsumptionCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new ConsumptionCrudController();
        $filtersConfig = Filters::new();
        $result = $controller->configureFilters($filtersConfig);
        self::assertSame($filtersConfig, $result);
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form);

        // 验证返回422状态码表示验证失败
        self::assertResponseStatusCodeSame(422);

        // 验证页面包含验证错误信息
        $errorText = $crawler->filter('.invalid-feedback')->text();
        self::assertStringContainsString('should not be blank', $errorText);
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new ConsumptionCrudController();
        self::assertEquals(Consumption::class, $controller::getEntityFqcn());
    }
}
