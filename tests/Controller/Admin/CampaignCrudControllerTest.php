<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\CampaignCrudController;
use PrepaidCardBundle\Entity\Campaign;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(CampaignCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CampaignCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CampaignCrudController
    {
        return self::getService(CampaignCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '活动名称' => ['活动名称'];
        yield '所属公司' => ['所属公司'];
        yield '开始时间' => ['开始时间'];
        yield '结束时间' => ['结束时间'];
        yield '有效状态' => ['有效状态'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'company' => ['company'];
        yield 'startTime' => ['startTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'valid' => ['valid'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'company' => ['company'];
        yield 'startTime' => ['startTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'valid' => ['valid'];
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Campaign CRUD
        $link = $crawler->filter('a[href*="CampaignCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateCampaign(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new CampaignCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditCampaign(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new CampaignCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailCampaign(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new CampaignCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new CampaignCrudController();
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
        $controller = new CampaignCrudController();
        self::assertEquals(Campaign::class, $controller::getEntityFqcn());
    }
}
