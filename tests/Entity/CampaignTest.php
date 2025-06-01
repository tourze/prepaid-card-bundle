<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Package;

class CampaignTest extends TestCase
{
    private Campaign $campaign;

    protected function setUp(): void
    {
        $this->campaign = new Campaign();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->campaign->setTitle('春节活动');
        $this->assertEquals('春节活动', $this->campaign->getTitle());

        $startTime = new \DateTime('2024-01-01');
        $this->campaign->setStartTime($startTime);
        $this->assertEquals($startTime, $this->campaign->getStartTime());

        $expireTime = new \DateTime('2024-12-31');
        $this->campaign->setExpireTime($expireTime);
        $this->assertEquals($expireTime, $this->campaign->getExpireTime());

        $giveCouponIds = ['COUPON001', 'COUPON002'];
        $this->campaign->setGiveCouponIds($giveCouponIds);
        $this->assertEquals($giveCouponIds, $this->campaign->getGiveCouponIds());

        $this->campaign->setValid(true);
        $this->assertTrue($this->campaign->isValid());

        $createTime = new \DateTime();
        $this->campaign->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->campaign->getCreateTime());

        $updateTime = new \DateTime();
        $this->campaign->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->campaign->getUpdateTime());

        $this->campaign->setCreatedBy('admin');
        $this->assertEquals('admin', $this->campaign->getCreatedBy());

        $this->campaign->setUpdatedBy('admin2');
        $this->assertEquals('admin2', $this->campaign->getUpdatedBy());
    }

    public function testCompanyRelationship(): void
    {
        /** @var Company&MockObject $company */
        $company = $this->createMock(Company::class);
        $this->campaign->setCompany($company);
        $this->assertSame($company, $this->campaign->getCompany());

        // 测试设置为null
        $this->campaign->setCompany(null);
        $this->assertNull($this->campaign->getCompany());
    }

    public function testPackagesCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->campaign->getPackages());
        $this->assertCount(0, $this->campaign->getPackages());

        // 添加码包
        /** @var Package&MockObject $package1 */
        $package1 = $this->createMock(Package::class);
        $package1->expects($this->once())->method('setCampaign')->with($this->campaign);
        
        $this->campaign->addPackage($package1);
        $this->assertCount(1, $this->campaign->getPackages());
        $this->assertTrue($this->campaign->getPackages()->contains($package1));

        // 重复添加同一个码包不应增加数量
        $this->campaign->addPackage($package1);
        $this->assertCount(1, $this->campaign->getPackages());
    }

    public function testPackagesCollectionRemove(): void
    {
        /** @var Package&MockObject $package1 */
        $package1 = $this->createMock(Package::class);
        $package1->expects($this->exactly(2))->method('setCampaign'); // 第一次设置为campaign，第二次设置为null
        
        $this->campaign->addPackage($package1);
        
        // 移除码包
        $package1->expects($this->once())->method('getCampaign')->willReturn($this->campaign);
        
        $this->campaign->removePackage($package1);
        $this->assertCount(0, $this->campaign->getPackages());
        $this->assertFalse($this->campaign->getPackages()->contains($package1));
    }

    public function testCardsCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->campaign->getCards());
        $this->assertCount(0, $this->campaign->getCards());

        // 添加卡片
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->once())->method('setCampaign')->with($this->campaign);
        
        $this->campaign->addCard($card1);
        $this->assertCount(1, $this->campaign->getCards());
        $this->assertTrue($this->campaign->getCards()->contains($card1));

        // 重复添加同一张卡片不应增加数量
        $this->campaign->addCard($card1);
        $this->assertCount(1, $this->campaign->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->exactly(2))->method('setCampaign'); // 第一次设置为campaign，第二次设置为null
        
        $this->campaign->addCard($card1);

        // 移除卡片
        $card1->expects($this->once())->method('getCampaign')->willReturn($this->campaign);
        
        $this->campaign->removeCard($card1);
        $this->assertCount(0, $this->campaign->getCards());
        $this->assertFalse($this->campaign->getCards()->contains($card1));
    }

    public function testGiveCouponIdsWithNull(): void
    {
        // 设置为null时应返回空数组
        $this->campaign->setGiveCouponIds(null);
        $this->assertEquals([], $this->campaign->getGiveCouponIds());

        // 设置空数组
        $this->campaign->setGiveCouponIds([]);
        $this->assertEquals([], $this->campaign->getGiveCouponIds());
    }

    public function testGiveCouponIdsWithData(): void
    {
        $couponIds = ['COUPON001', 'COUPON002', 'COUPON003'];
        $this->campaign->setGiveCouponIds($couponIds);
        $this->assertEquals($couponIds, $this->campaign->getGiveCouponIds());
    }

    public function testRetrieveAdminArray(): void
    {
        // 设置测试数据
        $this->campaign->setTitle('测试活动');
        $startTime = new \DateTime('2024-01-01 10:00:00');
        $this->campaign->setStartTime($startTime);
        $expireTime = new \DateTime('2024-12-31 23:59:59');
        $this->campaign->setExpireTime($expireTime);
        $this->campaign->setValid(true);
        $this->campaign->setGiveCouponIds(['COUPON001']);

        $createTime = new \DateTime('2024-01-01 08:00:00');
        $this->campaign->setCreateTime($createTime);
        $updateTime = new \DateTime('2024-01-01 09:00:00');
        $this->campaign->setUpdateTime($updateTime);

        // 模拟公司
        /** @var Company&MockObject $company */
        $company = $this->createMock(Company::class);
        $company->expects($this->once())
            ->method('retrieveAdminArray')
            ->willReturn(['id' => '1', 'title' => '测试公司']);
        $this->campaign->setCompany($company);

        $array = $this->campaign->retrieveAdminArray();

        $this->assertIsArray($array);
        $this->assertEquals('测试活动', $array['title']);
        $this->assertEquals('2024-01-01 10:00:00', $array['startTime']);
        $this->assertEquals('2024-12-31 23:59:59', $array['expireTime']);
        $this->assertTrue($array['valid']);
        $this->assertEquals(['COUPON001'], $array['giveCouponIds']);
        $this->assertEquals('2024-01-01 08:00:00', $array['createTime']);
        $this->assertEquals('2024-01-01 09:00:00', $array['updateTime']);
        $this->assertIsArray($array['company']);
    }

    public function testRetrieveApiArray(): void
    {
        $this->campaign->setTitle('测试活动');

        $array = $this->campaign->retrieveApiArray();

        $this->assertIsArray($array);
        $this->assertEquals('测试活动', $array['title']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testDefaultValues(): void
    {
        $campaign = new Campaign();
        
        // 默认值测试
        $this->assertNull($campaign->getId());
        $this->assertFalse($campaign->isValid());
        $this->assertNull($campaign->getCompany());
        $this->assertNull($campaign->getStartTime());
        $this->assertNull($campaign->getExpireTime());
        $this->assertEquals([], $campaign->getGiveCouponIds());
        $this->assertInstanceOf(Collection::class, $campaign->getPackages());
        $this->assertInstanceOf(Collection::class, $campaign->getCards());
        $this->assertCount(0, $campaign->getPackages());
        $this->assertCount(0, $campaign->getCards());
    }

    public function testTimeHandling(): void
    {
        // 测试时间处理
        $startTime = new \DateTime('2024-01-01 00:00:00');
        $expireTime = new \DateTime('2024-12-31 23:59:59');

        $this->campaign->setStartTime($startTime);
        $this->campaign->setExpireTime($expireTime);

        $this->assertEquals($startTime, $this->campaign->getStartTime());
        $this->assertEquals($expireTime, $this->campaign->getExpireTime());

        // 测试null值
        $this->campaign->setStartTime(null);
        $this->campaign->setExpireTime(null);
        
        $this->assertNull($this->campaign->getStartTime());
        $this->assertNull($this->campaign->getExpireTime());
    }
} 