<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Company::class)]
final class CompanyTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Company();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'title' => ['title', '测试公司'],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
            'createdBy' => ['createdBy', 'admin'],
            'updatedBy' => ['updatedBy', 'admin2'],
        ];
    }

    public function testCampaignsCollectionAdd(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $company->getCampaigns());
        $this->assertCount(0, $company->getCampaigns());

        // 添加活动 - 使用真实的Campaign实例
        $campaign1 = new Campaign();
        $campaign1->setTitle('测试活动');

        $company->addCampaign($campaign1);
        $this->assertCount(1, $company->getCampaigns());
        $this->assertTrue($company->getCampaigns()->contains($campaign1));
        // 验证双向关联已正确设置
        $this->assertSame($company, $campaign1->getCompany());

        // 重复添加同一个活动不应增加数量
        $company->addCampaign($campaign1);
        $this->assertCount(1, $company->getCampaigns());
    }

    public function testCampaignsCollectionRemove(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);

        // 使用真实的Campaign实例
        $campaign1 = new Campaign();
        $campaign1->setTitle('测试活动');

        $company->addCampaign($campaign1);
        // 验证添加成功
        $this->assertCount(1, $company->getCampaigns());
        $this->assertSame($company, $campaign1->getCompany());

        // 移除活动
        $company->removeCampaign($campaign1);
        $this->assertCount(0, $company->getCampaigns());
        $this->assertFalse($company->getCampaigns()->contains($campaign1));
        // 验证双向关联已正确清除
        $this->assertNull($campaign1->getCompany());
    }

    public function testCardsCollectionAdd(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $company->getCards());
        $this->assertCount(0, $company->getCards());

        // 添加卡片 - 使用真实的Card实例
        $card1 = new Card();
        $card1->setCardNumber('TEST_CARD_001');

        $company->addCard($card1);
        $this->assertCount(1, $company->getCards());
        $this->assertTrue($company->getCards()->contains($card1));
        // 验证双向关联已正确设置
        $this->assertSame($company, $card1->getCompany());

        // 重复添加同一张卡片不应增加数量
        $company->addCard($card1);
        $this->assertCount(1, $company->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);

        // 使用真实的Card实例
        $card1 = new Card();
        $card1->setCardNumber('TEST_CARD_001');

        $company->addCard($card1);
        // 验证添加成功
        $this->assertCount(1, $company->getCards());
        $this->assertSame($company, $card1->getCompany());

        // 移除卡片
        $company->removeCard($card1);
        $this->assertCount(0, $company->getCards());
        $this->assertFalse($company->getCards()->contains($card1));
        // 验证双向关联已正确清除
        $this->assertNull($card1->getCompany());
    }

    public function testToString(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);
        $company->setTitle('测试公司');

        // 测试没有ID时（新创建的实体）
        $this->assertEquals('', (string) $company);

        // 使用反射设置ID来测试有ID的情况
        $reflection = new \ReflectionClass($company);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($company, 123);

        $this->assertEquals('测试公司', (string) $company);
    }

    public function testRetrieveAdminArray(): void
    {
        $company = $this->createEntity();
        self::assertInstanceOf(Company::class, $company);
        $company->setTitle('测试公司');

        $array = $company->retrieveAdminArray();
        $this->assertEquals('测试公司', $array['title']);
    }
}
