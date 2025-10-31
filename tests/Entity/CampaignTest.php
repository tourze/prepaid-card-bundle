<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Package;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Campaign::class)]
final class CampaignTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Campaign();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'title' => ['title', '春节活动'],
            'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01')],
            'expireTime' => ['expireTime', new \DateTimeImmutable('2024-12-31')],
            'giveCouponIds' => ['giveCouponIds', ['COUPON001', 'COUPON002']],
            'valid' => ['valid', true],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
            'createdBy' => ['createdBy', 'admin'],
            'updatedBy' => ['updatedBy', 'admin2'],
        ];
    }

    public function testCompanyRelationship(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);
        /*
         * 使用匿名类实现 Company 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. Campaign 与 Company 的关系测试需要验证实际关联关系
         * 3. 匿名类提供可控的测试数据且不依赖数据库
         * 4. 简化关联关系测试的复杂性
         */
        $company = new class extends Company {
            // 匿名Company类用于关联关系测试
        };
        $campaign->setCompany($company);
        $this->assertSame($company, $campaign->getCompany());

        // 测试设置为null
        $campaign->setCompany(null);
        $this->assertNull($campaign->getCompany());
    }

    public function testPackagesCollectionAdd(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $campaign->getPackages());
        $this->assertCount(0, $campaign->getPackages());

        // 添加码包
        /*
         * 使用匿名类实现 Package 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. Package 是业务实体类，包含与 Campaign 的双向关联关系
         * 3. 需要测试 setCampaign() 等关联方法的调用
         * 4. 匿名类可以跟踪方法调用次数
         */
        $setCampaignCallCount = 0;
        $package1 = new class($setCampaignCallCount) extends Package {
            public function __construct(private int &$callCountRef)
            {
                parent::__construct();
            }

            public function setCampaign(?Campaign $campaign): void
            {
                ++$this->callCountRef;
                parent::setCampaign($campaign);
            }
        };

        $campaign->addPackage($package1);
        $this->assertCount(1, $campaign->getPackages());
        $this->assertTrue($campaign->getPackages()->contains($package1));
        $this->assertEquals(1, $setCampaignCallCount, 'setCampaign should be called once');

        // 重复添加同一个码包不应增加数量
        $campaign->addPackage($package1);
        $this->assertCount(1, $campaign->getPackages());
    }

    public function testPackagesCollectionRemove(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);
        /*
         * 使用匿名类实现 Package 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. Package 是业务实体类，包含与 Campaign 的双向关联关系
         * 3. 需要测试 setCampaign() 和 getCampaign() 等关联方法的调用
         * 4. 匿名类可以跟踪方法调用并返回预期值
         */
        $setCampaignCallCount = 0;
        $getCampaignCallCount = 0;
        $package1 = new class($setCampaignCallCount, $getCampaignCallCount) extends Package {
            private ?Campaign $campaign = null;

            public function __construct(
                private int &$setCampaignCallCountRef,
                private int &$getCampaignCallCountRef,
            ) {
                parent::__construct();
            }

            public function setCampaign(?Campaign $campaign): void
            {
                ++$this->setCampaignCallCountRef;
                $this->campaign = $campaign;
            }

            public function getCampaign(): ?Campaign
            {
                ++$this->getCampaignCallCountRef;

                return $this->campaign;
            }
        };

        $campaign->addPackage($package1);

        // 移除码包
        $campaign->removePackage($package1);
        $this->assertCount(0, $campaign->getPackages());
        $this->assertFalse($campaign->getPackages()->contains($package1));
        $this->assertEquals(2, $setCampaignCallCount, 'setCampaign should be called twice (add and remove)');
        $this->assertEquals(1, $getCampaignCallCount, 'getCampaign should be called once during remove');
    }

    public function testCardsCollectionAdd(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $campaign->getCards());
        $this->assertCount(0, $campaign->getCards());

        // 添加卡片
        /*
         * 使用匿名类实现 Card 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. Card 是业务实体类，包含与 Campaign 的双向关联关系
         * 3. 需要测试 setCampaign() 等关联方法的调用
         * 4. 匿名类可以跟踪方法调用次数
         */
        $setCampaignCallCount = 0;
        $card1 = new class($setCampaignCallCount) extends Card {
            public function __construct(private int &$callCountRef)
            {
                parent::__construct();
            }

            public function setCampaign(?Campaign $campaign): void
            {
                ++$this->callCountRef;
                parent::setCampaign($campaign);
            }
        };

        $campaign->addCard($card1);
        $this->assertCount(1, $campaign->getCards());
        $this->assertTrue($campaign->getCards()->contains($card1));
        $this->assertEquals(1, $setCampaignCallCount, 'setCampaign should be called once');

        // 重复添加同一张卡片不应增加数量
        $campaign->addCard($card1);
        $this->assertCount(1, $campaign->getCards());
    }

    public function testCardsCollectionRemove(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);
        /*
         * 使用匿名类实现 Card 的原因：
         * 1. 遵循 symplify.noTestMocks 规则，禁用 createMock()
         * 2. Card 是业务实体类，包含与 Campaign 的双向关联关系
         * 3. 需要测试 setCampaign() 和 getCampaign() 等关联方法的调用
         * 4. 匿名类可以跟踪方法调用并返回预期值
         */
        $setCampaignCallCount = 0;
        $getCampaignCallCount = 0;
        $card1 = new class($setCampaignCallCount, $getCampaignCallCount) extends Card {
            private ?Campaign $campaign = null;

            public function __construct(
                private int &$setCampaignCallCountRef,
                private int &$getCampaignCallCountRef,
            ) {
                parent::__construct();
            }

            public function setCampaign(?Campaign $campaign): void
            {
                ++$this->setCampaignCallCountRef;
                $this->campaign = $campaign;
                parent::setCampaign($campaign);
            }

            public function getCampaign(): ?Campaign
            {
                ++$this->getCampaignCallCountRef;

                return $this->campaign;
            }
        };

        $campaign->addCard($card1);

        // 移除卡片
        $campaign->removeCard($card1);
        $this->assertCount(0, $campaign->getCards());
        $this->assertFalse($campaign->getCards()->contains($card1));
        $this->assertEquals(2, $setCampaignCallCount, 'setCampaign should be called twice (add and remove)');
        $this->assertEquals(1, $getCampaignCallCount, 'getCampaign should be called once during remove');
    }

    public function testGiveCouponIdsWithNull(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        // 设置为null时应返回空数组
        $campaign->setGiveCouponIds(null);
        $this->assertEquals([], $campaign->getGiveCouponIds());

        // 设置空数组
        $campaign->setGiveCouponIds([]);
        $this->assertEquals([], $campaign->getGiveCouponIds());
    }

    public function testGiveCouponIdsWithData(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        $couponIds = [1001, 1002, 1003];
        $campaign->setGiveCouponIds($couponIds);
        $this->assertEquals($couponIds, $campaign->getGiveCouponIds());
    }

    public function testRetrieveAdminArray(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        // 设置测试数据
        $campaign->setTitle('测试活动');
        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $campaign->setStartTime($startTime);
        $expireTime = new \DateTimeImmutable('2024-12-31 23:59:59');
        $campaign->setExpireTime($expireTime);
        $campaign->setValid(true);
        $campaign->setGiveCouponIds([1001]);

        $createTime = new \DateTimeImmutable('2024-01-01 08:00:00');
        $campaign->setCreateTime($createTime);
        $updateTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $campaign->setUpdateTime($updateTime);

        // 使用真实的Company实例
        $company = new Company();
        $company->setTitle('测试公司');
        // 使用反射设置ID以便retrieveAdminArray能正常工作
        $reflection = new \ReflectionClass($company);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($company, '1');
        $campaign->setCompany($company);

        $array = $campaign->retrieveAdminArray();
        $this->assertEquals('测试活动', $array['title']);
        $this->assertEquals('2024-01-01 10:00:00', $array['startTime']);
        $this->assertEquals('2024-12-31 23:59:59', $array['expireTime']);
        $this->assertTrue($array['valid']);
        $this->assertEquals([1001], $array['giveCouponIds']);
        $this->assertEquals('2024-01-01 08:00:00', $array['createTime']);
        $this->assertEquals('2024-01-01 09:00:00', $array['updateTime']);
    }

    public function testRetrieveApiArray(): void
    {
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        $campaign->setTitle('测试活动');

        $array = $campaign->retrieveApiArray();
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
        $campaign = $this->createEntity();
        self::assertInstanceOf(Campaign::class, $campaign);

        // 测试时间处理
        $startTime = new \DateTimeImmutable('2024-01-01 00:00:00');
        $expireTime = new \DateTimeImmutable('2024-12-31 23:59:59');

        $campaign->setStartTime($startTime);
        $campaign->setExpireTime($expireTime);

        $this->assertEquals($startTime, $campaign->getStartTime());
        $this->assertEquals($expireTime, $campaign->getExpireTime());

        // 测试null值
        $campaign->setStartTime(null);
        $campaign->setExpireTime(null);

        $this->assertNull($campaign->getStartTime());
        $this->assertNull($campaign->getExpireTime());
    }
}
