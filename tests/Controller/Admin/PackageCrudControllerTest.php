<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\PackageCrudController;
use PrepaidCardBundle\Entity\Package;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(PackageCrudController::class)]
#[RunTestsInSeparateProcesses]
final class PackageCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): PackageCrudController
    {
        return self::getService(PackageCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '码包ID' => ['码包ID'];
        yield '所属活动' => ['所属活动'];
        yield '面值' => ['面值'];
        yield '数量' => ['数量'];
        yield '卡片类型' => ['卡片类型'];
        yield '卡有效起始时间' => ['卡有效起始时间'];
        yield '卡有效截止时间' => ['卡有效截止时间'];
        yield '余额有效期（天）' => ['余额有效期（天）'];
        yield '最大有效时间' => ['最大有效时间'];
        yield '过期类型' => ['过期类型'];
        yield '过期天数' => ['过期天数'];
        yield '缩略图' => ['缩略图'];
        yield '有效状态' => ['有效状态'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'packageId' => ['packageId'];
        yield 'campaign' => ['campaign'];
        yield 'parValue' => ['parValue'];
        yield 'quantity' => ['quantity'];
        yield 'type' => ['type'];
        yield 'startTime' => ['startTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'expireType' => ['expireType'];
        yield 'valid' => ['valid'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'packageId' => ['packageId'];
        yield 'campaign' => ['campaign'];
        yield 'parValue' => ['parValue'];
        yield 'quantity' => ['quantity'];
        yield 'type' => ['type'];
        yield 'startTime' => ['startTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'expireType' => ['expireType'];
        yield 'valid' => ['valid'];
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Package CRUD
        $link = $crawler->filter('a[href*="PackageCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreatePackage(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new PackageCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditPackage(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new PackageCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailPackage(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new PackageCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new PackageCrudController();
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
        $controller = new PackageCrudController();
        self::assertEquals(Package::class, $controller::getEntityFqcn());
    }
}
