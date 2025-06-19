<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;

class CompanyTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        $this->company = new Company();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->company->setTitle('测试公司');
        $this->assertEquals('测试公司', $this->company->getTitle());

        $createTime = new \DateTimeImmutable();
        $this->company->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->company->getCreateTime());

        $updateTime = new \DateTimeImmutable();
        $this->company->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->company->getUpdateTime());

        $this->company->setCreatedBy('admin');
        $this->assertEquals('admin', $this->company->getCreatedBy());

        $this->company->setUpdatedBy('admin2');
        $this->assertEquals('admin2', $this->company->getUpdatedBy());
    }

    public function testCampaignsCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->company->getCampaigns());
        $this->assertCount(0, $this->company->getCampaigns());

        // 添加活动
        /** @var Campaign&MockObject $campaign1 */
        $campaign1 = $this->createMock(Campaign::class);
        $campaign1->expects($this->once())->method('setCompany')->with($this->company);
        
        $this->company->addCampaign($campaign1);
        $this->assertCount(1, $this->company->getCampaigns());
        $this->assertTrue($this->company->getCampaigns()->contains($campaign1));

        // 重复添加同一个活动不应增加数量
        $this->company->addCampaign($campaign1);
        $this->assertCount(1, $this->company->getCampaigns());
    }

    public function testCampaignsCollectionRemove(): void
    {
        /** @var Campaign&MockObject $campaign1 */
        $campaign1 = $this->createMock(Campaign::class);
        $campaign1->expects($this->exactly(2))->method('setCompany'); // 第一次设置为company，第二次设置为null
        
        $this->company->addCampaign($campaign1);

        // 移除活动
        $campaign1->expects($this->once())->method('getCompany')->willReturn($this->company);
        
        $this->company->removeCampaign($campaign1);
        $this->assertCount(0, $this->company->getCampaigns());
        $this->assertFalse($this->company->getCampaigns()->contains($campaign1));
    }

    public function testCardsCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->company->getCards());
        $this->assertCount(0, $this->company->getCards());

        // 添加卡片
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->once())->method('setCompany')->with($this->company);
        
        $this->company->addCard($card1);
        $this->assertCount(1, $this->company->getCards());
        $this->assertTrue($this->company->getCards()->contains($card1));

        // 重复添加同一张卡片不应增加数量
        $this->company->addCard($card1);
        $this->assertCount(1, $this->company->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->exactly(2))->method('setCompany'); // 第一次设置为company，第二次设置为null
        
        $this->company->addCard($card1);

        // 移除卡片
        $card1->expects($this->once())->method('getCompany')->willReturn($this->company);
        
        $this->company->removeCard($card1);
        $this->assertCount(0, $this->company->getCards());
        $this->assertFalse($this->company->getCards()->contains($card1));
    }

    public function testToString(): void
    {
        // 测试没有ID时
        $company = new Company();
        $this->assertEquals('', (string) $company);

        // 设置标题后
        $this->company->setTitle('测试公司名称');
        // 由于没有实际的ID，toString会返回空字符串
        // 这里我们主要测试方法不会抛出异常
        $result = (string) $this->company;
    }

    public function testRetrieveAdminArray(): void
    {
        // 设置测试数据
        $this->company->setTitle('测试公司');
        
        $createTime = new \DateTimeImmutable('2024-01-01 08:00:00');
        $this->company->setCreateTime($createTime);
        $updateTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $this->company->setUpdateTime($updateTime);

        $array = $this->company->retrieveAdminArray();
        $this->assertEquals('测试公司', $array['title']);
        $this->assertEquals('2024-01-01 08:00:00', $array['createTime']);
        $this->assertEquals('2024-01-01 09:00:00', $array['updateTime']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testToSelectItem(): void
    {
        $this->company->setTitle('选择项测试公司');

        $selectItem = $this->company->toSelectItem();
        $this->assertEquals('选择项测试公司', $selectItem['label']);
        $this->assertEquals('选择项测试公司', $selectItem['text']);
        $this->assertEquals('选择项测试公司', $selectItem['name']);
        $this->assertArrayHasKey('value', $selectItem);
    }

    public function testDefaultValues(): void
    {
        $company = new Company();
        
        // 默认值测试
        $this->assertNull($company->getId());
        $this->assertNull($company->getCreatedBy());
        $this->assertNull($company->getUpdatedBy());
        $this->assertNull($company->getCreateTime());
        $this->assertNull($company->getUpdateTime());
        $this->assertInstanceOf(Collection::class, $company->getCampaigns());
        $this->assertInstanceOf(Collection::class, $company->getCards());
        $this->assertCount(0, $company->getCampaigns());
        $this->assertCount(0, $company->getCards());
    }

    public function testUniqueTitle(): void
    {
        // 测试标题设置
        $title1 = '公司A';
        $title2 = '公司B';

        $this->company->setTitle($title1);
        $this->assertEquals($title1, $this->company->getTitle());

        $this->company->setTitle($title2);
        $this->assertEquals($title2, $this->company->getTitle());
    }

    public function testTimeHandling(): void
    {
        // 测试时间处理
        $createTime = new \DateTimeImmutable('2024-01-01 00:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        $this->company->setCreateTime($createTime);
        $this->company->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $this->company->getCreateTime());
        $this->assertEquals($updateTime, $this->company->getUpdateTime());

        // 测试null值
        $this->company->setCreateTime(null);
        $this->company->setUpdateTime(null);
        
        $this->assertNull($this->company->getCreateTime());
        $this->assertNull($this->company->getUpdateTime());
    }

    public function testUserTracking(): void
    {
        // 测试用户追踪
        $this->company->setCreatedBy('user1');
        $this->company->setUpdatedBy('user2');

        $this->assertEquals('user1', $this->company->getCreatedBy());
        $this->assertEquals('user2', $this->company->getUpdatedBy());

        // 测试null值
        $this->company->setCreatedBy(null);
        $this->company->setUpdatedBy(null);
        
        $this->assertNull($this->company->getCreatedBy());
        $this->assertNull($this->company->getUpdatedBy());
    }
} 