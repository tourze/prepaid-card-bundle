<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;

class PackageTest extends TestCase
{
    private Package $package;

    protected function setUp(): void
    {
        $this->package = new Package();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->package->setPackageId('PKG001');
        $this->assertEquals('PKG001', $this->package->getPackageId());

        $this->package->setParValue('100.00');
        $this->assertEquals('100.00', $this->package->getParValue());

        $this->package->setQuantity(50);
        $this->assertEquals(50, $this->package->getQuantity());

        $this->package->setExpireDays(365);
        $this->assertEquals(365, $this->package->getExpireDays());

        $this->package->setExpireNum(30);
        $this->assertEquals(30, $this->package->getExpireNum());

        $this->package->setThumbUrl('https://example.com/thumb.jpg');
        $this->assertEquals('https://example.com/thumb.jpg', $this->package->getThumbUrl());

        $this->package->setValid(true);
        $this->assertTrue($this->package->isValid());

        $this->package->setCreatedBy('admin');
        $this->assertEquals('admin', $this->package->getCreatedBy());

        $this->package->setUpdatedBy('admin2');
        $this->assertEquals('admin2', $this->package->getUpdatedBy());
    }

    public function testCampaignRelationship(): void
    {
        /** @var Campaign&MockObject $campaign */
        $campaign = $this->createMock(Campaign::class);
        $this->package->setCampaign($campaign);
        $this->assertSame($campaign, $this->package->getCampaign());

        // 测试设置为null
        $this->package->setCampaign(null);
        $this->assertNull($this->package->getCampaign());
    }

    public function testCardsCollectionAdd(): void
    {
        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->package->getCards());
        $this->assertCount(0, $this->package->getCards());

        // 添加卡片
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->once())->method('setPackage')->with($this->package);
        
        $this->package->addCard($card1);
        $this->assertCount(1, $this->package->getCards());
        $this->assertTrue($this->package->getCards()->contains($card1));

        // 重复添加同一张卡片不应增加数量
        $this->package->addCard($card1);
        $this->assertCount(1, $this->package->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        /** @var Card&MockObject $card1 */
        $card1 = $this->createMock(Card::class);
        $card1->expects($this->exactly(2))->method('setPackage'); // 第一次设置为package，第二次设置为null
        
        $this->package->addCard($card1);

        // 移除卡片
        $card1->expects($this->once())->method('getPackage')->willReturn($this->package);
        
        $this->package->removeCard($card1);
        $this->assertCount(0, $this->package->getCards());
        $this->assertFalse($this->package->getCards()->contains($card1));
    }

    public function testPrepaidCardTypeEnum(): void
    {
        // 测试设置和获取PrepaidCardType枚举
        $this->package->setType(PrepaidCardType::ONE_TIME);
        $this->assertEquals(PrepaidCardType::ONE_TIME, $this->package->getType());

        $this->package->setType(PrepaidCardType::AFTER);
        $this->assertEquals(PrepaidCardType::AFTER, $this->package->getType());
    }

    public function testPrepaidCardExpireTypeEnum(): void
    {
        // 测试设置和获取PrepaidCardExpireType枚举
        $this->package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $this->assertEquals(PrepaidCardExpireType::SAME_WITH_CARD, $this->package->getExpireType());

        $this->package->setExpireType(PrepaidCardExpireType::AFTER_ACTIVATION);
        $this->assertEquals(PrepaidCardExpireType::AFTER_ACTIVATION, $this->package->getExpireType());
    }

    public function testTimeHandling(): void
    {
        // 测试时间字段处理
        $startTime = new \DateTime('2024-01-01 00:00:00');
        $expireTime = new \DateTime('2024-12-31 23:59:59');
        $maxValidTime = new \DateTime('2025-06-30 12:00:00');
        $createTime = new \DateTime('2024-01-01 08:00:00');
        $updateTime = new \DateTime('2024-01-01 09:00:00');

        $this->package->setStartTime($startTime);
        $this->package->setExpireTime($expireTime);
        $this->package->setMaxValidTime($maxValidTime);
        $this->package->setCreateTime($createTime);
        $this->package->setUpdateTime($updateTime);

        $this->assertEquals($startTime, $this->package->getStartTime());
        $this->assertEquals($expireTime, $this->package->getExpireTime());
        $this->assertEquals($maxValidTime, $this->package->getMaxValidTime());
        $this->assertEquals($createTime, $this->package->getCreateTime());
        $this->assertEquals($updateTime, $this->package->getUpdateTime());

        // 测试null值
        $this->package->setStartTime(null);
        $this->package->setExpireTime(null);
        $this->package->setMaxValidTime(null);
        $this->package->setCreateTime(null);
        $this->package->setUpdateTime(null);
        
        $this->assertNull($this->package->getStartTime());
        $this->assertNull($this->package->getExpireTime());
        $this->assertNull($this->package->getMaxValidTime());
        $this->assertNull($this->package->getCreateTime());
        $this->assertNull($this->package->getUpdateTime());
    }

    public function testRetrieveApiArray(): void
    {
        $this->package->setParValue('50.00');
        $this->package->setThumbUrl('https://example.com/api-thumb.jpg');

        $array = $this->package->retrieveApiArray();
        $this->assertEquals('50.00', $array['parValue']);
        $this->assertEquals('https://example.com/api-thumb.jpg', $array['thumbUrl']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testRetrieveAdminArray(): void
    {
        // 设置测试数据
        $this->package->setPackageId('ADMIN_PKG_001');
        $this->package->setParValue('200.00');
        $this->package->setQuantity(100);
        $this->package->setExpireDays(90);
        $this->package->setExpireNum(15);
        $this->package->setThumbUrl('https://example.com/admin-thumb.jpg');
        $this->package->setValid(true);
        $this->package->setType(PrepaidCardType::ONE_TIME);
        $this->package->setExpireType(PrepaidCardExpireType::AFTER_ACTIVATION);

        $startTime = new \DateTime('2024-02-01 10:00:00');
        $expireTime = new \DateTime('2024-11-30 18:00:00');
        $maxValidTime = new \DateTime('2025-05-15 23:59:59');
        $createTime = new \DateTime('2024-01-15 14:30:45');
        $updateTime = new \DateTime('2024-01-20 16:45:30');

        $this->package->setStartTime($startTime);
        $this->package->setExpireTime($expireTime);
        $this->package->setMaxValidTime($maxValidTime);
        $this->package->setCreateTime($createTime);
        $this->package->setUpdateTime($updateTime);

        $array = $this->package->retrieveAdminArray();
        $this->assertEquals('ADMIN_PKG_001', $array['packageId']);
        $this->assertEquals('200.00', $array['parValue']);
        $this->assertEquals(100, $array['quantity']);
        $this->assertEquals(90, $array['expireDays']);
        $this->assertEquals(15, $array['expireNum']);
        $this->assertEquals('https://example.com/admin-thumb.jpg', $array['thumbUrl']);
        $this->assertTrue($array['valid']);
        $this->assertEquals('one-time', $array['type']);
        $this->assertEquals(2, $array['expireType']);
        $this->assertEquals('2024-02-01 10:00:00', $array['startTime']);
        $this->assertEquals('2024-11-30 18:00:00', $array['expireTime']);
        $this->assertEquals('2025-05-15 23:59:59', $array['maxValidTime']);
        $this->assertEquals('2024-01-15 14:30:45', $array['createTime']);
        $this->assertEquals('2024-01-20 16:45:30', $array['updateTime']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testDefaultValues(): void
    {
        $package = new Package();
        
        // 默认值测试
        $this->assertNull($package->getId());
        $this->assertNull($package->getCampaign());
        $this->assertNull($package->getParValue());
        $this->assertNull($package->getStartTime());
        $this->assertNull($package->getExpireTime());
        $this->assertNull($package->getExpireDays());
        $this->assertNull($package->getMaxValidTime());
        $this->assertNull($package->getThumbUrl());
        $this->assertFalse($package->isValid());
        $this->assertNull($package->getCreatedBy());
        $this->assertNull($package->getUpdatedBy());
        $this->assertNull($package->getCreateTime());
        $this->assertNull($package->getUpdateTime());
        $this->assertInstanceOf(Collection::class, $package->getCards());
        $this->assertCount(0, $package->getCards());
    }

    public function testNullableFields(): void
    {
        // 测试可为null的字段
        $this->package->setParValue(null);
        $this->assertNull($this->package->getParValue());

        $this->package->setExpireDays(null);
        $this->assertNull($this->package->getExpireDays());

        $this->package->setThumbUrl(null);
        $this->assertNull($this->package->getThumbUrl());

        $this->package->setValid(null);
        $this->assertNull($this->package->isValid());

        $this->package->setCreatedBy(null);
        $this->assertNull($this->package->getCreatedBy());

        $this->package->setUpdatedBy(null);
        $this->assertNull($this->package->getUpdatedBy());
    }

    public function testParValueHandling(): void
    {
        // 测试不同格式的面值
        $values = [
            '10.00',
            '50.50',
            '100',
            '999.99',
            '0.01'
        ];

        foreach ($values as $value) {
            $this->package->setParValue($value);
            $this->assertEquals($value, $this->package->getParValue());
        }
    }

    public function testQuantityHandling(): void
    {
        // 测试数量设置
        $quantities = [1, 10, 50, 100, 1000, 99999];

        foreach ($quantities as $quantity) {
            $this->package->setQuantity($quantity);
            $this->assertEquals($quantity, $this->package->getQuantity());
        }
    }

    public function testExpireDaysHandling(): void
    {
        // 测试过期天数
        $days = [1, 7, 30, 90, 365, 730];

        foreach ($days as $day) {
            $this->package->setExpireDays($day);
            $this->assertEquals($day, $this->package->getExpireDays());
        }
    }

    public function testExpireNumHandling(): void
    {
        // 测试过期数字
        $nums = [0, 1, 15, 30, 60, 90, 365];

        foreach ($nums as $num) {
            $this->package->setExpireNum($num);
            $this->assertEquals($num, $this->package->getExpireNum());
        }
    }

    public function testThumbUrlFormats(): void
    {
        // 测试不同格式的缩略图URL
        $urls = [
            'https://example.com/image.jpg',
            'http://localhost/thumb.png',
            '/assets/thumb.gif',
            'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD',
            '../images/package_thumb.webp'
        ];

        foreach ($urls as $url) {
            $this->package->setThumbUrl($url);
            $this->assertEquals($url, $this->package->getThumbUrl());
        }
    }

    public function testPackageIdFormat(): void
    {
        // 测试码包ID格式
        $packageIds = [
            'PKG001',
            'PACKAGE_2024_001',
            'P240101001',
            'TEST-PKG-001',
            'pkg.2024.jan.001'
        ];

        foreach ($packageIds as $packageId) {
            $this->package->setPackageId($packageId);
            $this->assertEquals($packageId, $this->package->getPackageId());
        }
    }

    public function testValidStatus(): void
    {
        // 测试有效状态
        $this->package->setValid(true);
        $this->assertTrue($this->package->isValid());

        $this->package->setValid(false);
        $this->assertFalse($this->package->isValid());

        $this->package->setValid(null);
        $this->assertNull($this->package->isValid());
    }

    public function testUserTracking(): void
    {
        // 测试用户追踪
        $this->package->setCreatedBy('user1');
        $this->package->setUpdatedBy('user2');

        $this->assertEquals('user1', $this->package->getCreatedBy());
        $this->assertEquals('user2', $this->package->getUpdatedBy());

        // 测试null值
        $this->package->setCreatedBy(null);
        $this->package->setUpdatedBy(null);
        
        $this->assertNull($this->package->getCreatedBy());
        $this->assertNull($this->package->getUpdatedBy());
    }
} 