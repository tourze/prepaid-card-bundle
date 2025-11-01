<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\CompanyCrudController;
use PrepaidCardBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(CompanyCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CompanyCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CompanyCrudController
    {
        return self::getService(CompanyCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '公司名称' => ['公司名称'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
    }

    public function testIndexPage(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Company CRUD
        $link = $crawler->filter('a[href*="CompanyCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateCompany(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new CompanyCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditCompany(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new CompanyCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailCompany(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new CompanyCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new CompanyCrudController();
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
        $controller = new CompanyCrudController();
        self::assertEquals(Company::class, $controller::getEntityFqcn());
    }
}
