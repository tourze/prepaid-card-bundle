<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Controller\Admin\CardCrudController;
use PrepaidCardBundle\Entity\Card;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(CardCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CardCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CardCrudController
    {
        return self::getService(CardCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '卡号' => ['卡号'];
        yield '卡密' => ['卡密'];
        yield '所属公司' => ['所属公司'];
        yield '面值' => ['面值'];
        yield '余额' => ['余额'];
        yield '绑定时间' => ['绑定时间'];
        yield '过期时间' => ['过期时间'];
        yield '卡片持有者' => ['卡片持有者'];
        yield '状态' => ['状态'];
        yield '所属活动' => ['所属活动'];
        yield '所属套餐' => ['所属套餐'];
        yield '有效状态' => ['有效状态'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'cardNumber' => ['cardNumber'];
        yield 'cardPassword' => ['cardPassword'];
        yield 'company' => ['company'];
        yield 'parValue' => ['parValue'];
        yield 'balance' => ['balance'];
        yield 'bindTime' => ['bindTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'owner' => ['owner'];
        yield 'status' => ['status'];
        yield 'campaign' => ['campaign'];
        yield 'package' => ['package'];
        yield 'valid' => ['valid'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'cardNumber' => ['cardNumber'];
        yield 'cardPassword' => ['cardPassword'];
        yield 'company' => ['company'];
        yield 'parValue' => ['parValue'];
        yield 'balance' => ['balance'];
        yield 'bindTime' => ['bindTime'];
        yield 'expireTime' => ['expireTime'];
        yield 'owner' => ['owner'];
        yield 'status' => ['status'];
        yield 'campaign' => ['campaign'];
        yield 'package' => ['package'];
        yield 'valid' => ['valid'];
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to Card CRUD
        $link = $crawler->filter('a[href*="CardCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateCard(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new CardCrudController();
        $fields = $controller->configureFields('new');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testEditCard(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new CardCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailCard(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new CardCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and works correctly
        $controller = new CardCrudController();
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
        $controller = new CardCrudController();
        self::assertEquals(Card::class, $controller::getEntityFqcn());
    }
}
