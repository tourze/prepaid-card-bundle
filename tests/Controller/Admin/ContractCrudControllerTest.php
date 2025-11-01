<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\ContractCrudController;
use PrepaidCardBundle\Entity\Contract;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ContractCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ContractCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): ContractCrudController
    {
        return self::getService(ContractCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '订单编码' => ['订单编码'];
        yield '总费用' => ['总费用'];
        yield '退款时间' => ['退款时间'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'code' => ['code'];
        yield 'costAmount' => ['costAmount'];
        yield 'refundTime' => ['refundTime'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'code' => ['code'];
        yield 'costAmount' => ['costAmount'];
        yield 'refundTime' => ['refundTime'];
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Contract CRUD
        $link = $crawler->filter('a[href*="ContractCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateContract(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new ContractCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditContract(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new ContractCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailContract(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new ContractCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new ContractCrudController();
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
        $controller = new ContractCrudController();
        self::assertEquals(Contract::class, $controller::getEntityFqcn());
    }
}
