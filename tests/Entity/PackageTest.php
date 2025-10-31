<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Package::class)]
final class PackageTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Package();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'packageId' => ['packageId', 'PKG001'],
            'parValue' => ['parValue', '100.00'],
            'quantity' => ['quantity', 50],
            'expireDays' => ['expireDays', 365],
            'expireNum' => ['expireNum', 30],
            'thumbUrl' => ['thumbUrl', 'https://example.com/thumb.jpg'],
            'valid' => ['valid', true],
            'createdBy' => ['createdBy', 'admin'],
            'updatedBy' => ['updatedBy', 'admin2'],
            'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 00:00:00')],
            'expireTime' => ['expireTime', new \DateTimeImmutable('2024-12-31 23:59:59')],
            'maxValidTime' => ['maxValidTime', new \DateTimeImmutable('2025-06-30 12:00:00')],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    public function testCampaignRelationship(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 使用真实的Campaign实例
        $campaign = new Campaign();
        $campaign->setTitle('测试活动');
        $package->setCampaign($campaign);
        $this->assertSame($campaign, $package->getCampaign());

        // 测试设置为null
        $package->setCampaign(null);
        $this->assertNull($package->getCampaign());
    }

    public function testCardsCollectionAdd(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $package->getCards());
        $this->assertCount(0, $package->getCards());

        // 添加卡片 - 使用真实的Card实例
        $card1 = new Card();
        $card1->setCardNumber('TEST_CARD_001');
        $card1->setParValue('100.00');

        $package->addCard($card1);
        $this->assertCount(1, $package->getCards());
        $this->assertTrue($package->getCards()->contains($card1));
        // 验证双向关联已正确设置
        $this->assertSame($package, $card1->getPackage());

        // 重复添加同一张卡片不应增加数量
        $package->addCard($card1);
        $this->assertCount(1, $package->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 使用真实的Card实例
        $card1 = new Card();
        $card1->setCardNumber('TEST_CARD_001');
        $card1->setParValue('100.00');

        $package->addCard($card1);
        // 验证添加成功
        $this->assertCount(1, $package->getCards());
        $this->assertSame($package, $card1->getPackage());

        // 移除卡片
        $package->removeCard($card1);
        $this->assertCount(0, $package->getCards());
        $this->assertFalse($package->getCards()->contains($card1));
        // 验证双向关联已正确清除
        $this->assertNull($card1->getPackage());
    }

    public function testPrepaidCardTypeEnum(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试设置和获取PrepaidCardType枚举
        $package->setType(PrepaidCardType::ONE_TIME);
        $this->assertEquals(PrepaidCardType::ONE_TIME, $package->getType());

        $package->setType(PrepaidCardType::AFTER);
        $this->assertEquals(PrepaidCardType::AFTER, $package->getType());
    }

    public function testPrepaidCardExpireTypeEnum(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试设置和获取PrepaidCardExpireType枚举
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $this->assertEquals(PrepaidCardExpireType::SAME_WITH_CARD, $package->getExpireType());

        $package->setExpireType(PrepaidCardExpireType::AFTER_ACTIVATION);
        $this->assertEquals(PrepaidCardExpireType::AFTER_ACTIVATION, $package->getExpireType());
    }

    public function testTimeHandling(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试时间字段处理
        $startTime = new \DateTimeImmutable('2024-01-01 00:00:00');
        $expireTime = new \DateTimeImmutable('2024-12-31 23:59:59');
        $maxValidTime = new \DateTimeImmutable('2025-06-30 12:00:00');
        $createTime = new \DateTimeImmutable('2024-01-01 08:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-01 09:00:00');

        $package->setStartTime($startTime);
        $package->setExpireTime($expireTime);
        $package->setMaxValidTime($maxValidTime);
        $package->setCreateTime($createTime);
        $package->setUpdateTime($updateTime);

        $this->assertEquals($startTime, $package->getStartTime());
        $this->assertEquals($expireTime, $package->getExpireTime());
        $this->assertEquals($maxValidTime, $package->getMaxValidTime());
        $this->assertEquals($createTime, $package->getCreateTime());
        $this->assertEquals($updateTime, $package->getUpdateTime());

        // 测试null值
        $package->setStartTime(null);
        $package->setExpireTime(null);
        $package->setMaxValidTime(null);
        $package->setCreateTime(null);
        $package->setUpdateTime(null);

        $this->assertNull($package->getStartTime());
        $this->assertNull($package->getExpireTime());
        $this->assertNull($package->getMaxValidTime());
        $this->assertNull($package->getCreateTime());
        $this->assertNull($package->getUpdateTime());
    }

    public function testRetrieveApiArray(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        $package->setParValue('50.00');
        $package->setThumbUrl('https://example.com/api-thumb.jpg');

        $array = $package->retrieveApiArray();
        $this->assertEquals('50.00', $array['parValue']);
        $this->assertEquals('https://example.com/api-thumb.jpg', $array['thumbUrl']);
        $this->assertArrayHasKey('id', $array);
    }

    public function testRetrieveAdminArray(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 设置测试数据
        $package->setPackageId('ADMIN_PKG_001');
        $package->setParValue('200.00');
        $package->setQuantity(100);
        $package->setExpireDays(90);
        $package->setExpireNum(15);
        $package->setThumbUrl('https://example.com/admin-thumb.jpg');
        $package->setValid(true);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::AFTER_ACTIVATION);

        $startTime = new \DateTimeImmutable('2024-02-01 10:00:00');
        $expireTime = new \DateTimeImmutable('2024-11-30 18:00:00');
        $maxValidTime = new \DateTimeImmutable('2025-05-15 23:59:59');
        $createTime = new \DateTimeImmutable('2024-01-15 14:30:45');
        $updateTime = new \DateTimeImmutable('2024-01-20 16:45:30');

        $package->setStartTime($startTime);
        $package->setExpireTime($expireTime);
        $package->setMaxValidTime($maxValidTime);
        $package->setCreateTime($createTime);
        $package->setUpdateTime($updateTime);

        $array = $package->retrieveAdminArray();
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
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

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
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试可为null的字段
        $package->setParValue(null);
        $this->assertNull($package->getParValue());

        $package->setExpireDays(null);
        $this->assertNull($package->getExpireDays());

        $package->setThumbUrl(null);
        $this->assertNull($package->getThumbUrl());

        $package->setValid(null);
        $this->assertNull($package->isValid());

        $package->setCreatedBy(null);
        $this->assertNull($package->getCreatedBy());

        $package->setUpdatedBy(null);
        $this->assertNull($package->getUpdatedBy());
    }

    public function testParValueHandling(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试不同格式的面值
        $values = [
            '10.00',
            '50.50',
            '100',
            '999.99',
            '0.01',
        ];

        foreach ($values as $value) {
            $package->setParValue($value);
            $this->assertEquals($value, $package->getParValue());
        }
    }

    public function testQuantityHandling(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试数量设置
        $quantities = [1, 10, 50, 100, 1000, 99999];

        foreach ($quantities as $quantity) {
            $package->setQuantity($quantity);
            $this->assertEquals($quantity, $package->getQuantity());
        }
    }

    public function testExpireDaysHandling(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试过期天数
        $days = [1, 7, 30, 90, 365, 730];

        foreach ($days as $day) {
            $package->setExpireDays($day);
            $this->assertEquals($day, $package->getExpireDays());
        }
    }

    public function testExpireNumHandling(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试过期数字
        $nums = [0, 1, 15, 30, 60, 90, 365];

        foreach ($nums as $num) {
            $package->setExpireNum($num);
            $this->assertEquals($num, $package->getExpireNum());
        }
    }

    public function testThumbUrlFormats(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试不同格式的缩略图URL
        $urls = [
            'https://example.com/image.jpg',
            'http://localhost/thumb.png',
            '/assets/thumb.gif',
            'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD',
            '../images/package_thumb.webp',
        ];

        foreach ($urls as $url) {
            $package->setThumbUrl($url);
            $this->assertEquals($url, $package->getThumbUrl());
        }
    }

    public function testPackageIdFormat(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试码包ID格式
        $packageIds = [
            'PKG001',
            'PACKAGE_2024_001',
            'P240101001',
            'TEST-PKG-001',
            'pkg.2024.jan.001',
        ];

        foreach ($packageIds as $packageId) {
            $package->setPackageId($packageId);
            $this->assertEquals($packageId, $package->getPackageId());
        }
    }

    public function testValidStatus(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试有效状态
        $package->setValid(true);
        $this->assertTrue($package->isValid());

        $package->setValid(false);
        $this->assertFalse($package->isValid());

        $package->setValid(null);
        $this->assertNull($package->isValid());
    }

    public function testUserTracking(): void
    {
        $package = $this->createEntity();
        self::assertInstanceOf(Package::class, $package);

        // 测试用户追踪
        $package->setCreatedBy('user1');
        $package->setUpdatedBy('user2');

        $this->assertEquals('user1', $package->getCreatedBy());
        $this->assertEquals('user2', $package->getUpdatedBy());

        // 测试null值
        $package->setCreatedBy(null);
        $package->setUpdatedBy(null);

        $this->assertNull($package->getCreatedBy());
        $this->assertNull($package->getUpdatedBy());
    }
}
