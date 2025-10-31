<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Card::class)]
final class CardTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Card();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'cardNumber' => ['cardNumber', 'CARD123456'],
            'cardPassword' => ['cardPassword', 'password123'],
            'parValue' => ['parValue', '100.00'],
            'balance' => ['balance', '100.00'],
            'bindTime' => ['bindTime', new \DateTimeImmutable()],
            'expireTime' => ['expireTime', new \DateTimeImmutable('+1 year')],
            'status' => ['status', PrepaidCardStatus::VALID],
            'valid' => ['valid', true],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
            'createdBy' => ['createdBy', 'admin'],
        ];
    }

    public function testRelationships(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 测试关联关系 - 使用真实的Entity实例
        $company = new Company();
        $company->setTitle('测试公司');
        $card->setCompany($company);
        $this->assertSame($company, $card->getCompany());

        $campaign = new Campaign();
        $campaign->setTitle('测试活动');
        $card->setCampaign($campaign);
        $this->assertSame($campaign, $card->getCampaign());

        $package = new Package();
        $package->setPackageId('TEST_PACKAGE_001');
        $package->setParValue('100.00');
        $card->setPackage($package);
        $this->assertSame($package, $card->getPackage());

        // 使用匿名类UserInterface实现
        $owner = new class implements UserInterface {
            public function getUserIdentifier(): string
            {
                return 'test-user';
            }

            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
                // 没有需要清除的凭据
            }
        };
        $card->setOwner($owner);
        $this->assertSame($owner, $card->getOwner());
    }

    public function testAddAndRemoveConsumption(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 使用真实的Consumption实例
        $consumption = new Consumption();
        $consumption->setTitle('测试消费');
        $consumption->setOrderId('TXN_001');
        $consumption->setAmount('50.00');

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $card->getConsumptions());
        $this->assertCount(0, $card->getConsumptions());

        // 添加消费记录
        $card->addConsumption($consumption);
        $this->assertCount(1, $card->getConsumptions());
        $this->assertTrue($card->getConsumptions()->contains($consumption));
        // 验证双向关联已正确设置
        $this->assertSame($card, $consumption->getCard());

        // 移除消费记录
        $card->removeConsumption($consumption);
        $this->assertCount(0, $card->getConsumptions());
        $this->assertFalse($card->getConsumptions()->contains($consumption));
    }

    public function testCheckStatusWhenExpired(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 设置过期时间为过去
        $pastDate = new \DateTimeImmutable('-1 day');
        $card->setExpireTime($pastDate);
        $card->setBalance('100.00');
        $card->setStatus(PrepaidCardStatus::VALID);

        // 检查状态
        $card->checkStatus();

        // 应该变为过期状态
        $this->assertEquals(PrepaidCardStatus::EXPIRED, $card->getStatus());
    }

    public function testCheckStatusWhenEmpty(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 设置余额为0
        $futureDate = new \DateTimeImmutable('+1 day');
        $card->setExpireTime($futureDate);
        $card->setBalance('0.00');
        $card->setStatus(PrepaidCardStatus::VALID);

        // 检查状态
        $card->checkStatus();

        // 应该变为已用完状态
        $this->assertEquals(PrepaidCardStatus::EMPTY, $card->getStatus());
    }

    public function testCheckStatusWhenValid(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 设置有效卡片
        $futureDate = new \DateTimeImmutable('+1 day');
        $card->setExpireTime($futureDate);
        $card->setBalance('100.00');
        $card->setStatus(PrepaidCardStatus::INIT);

        // 检查状态
        $card->checkStatus();

        // 应该变为有效状态
        $this->assertEquals(PrepaidCardStatus::VALID, $card->getStatus());
    }

    public function testRetrieveApiArray(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 设置必要属性
        $card->setCardNumber('CARD123456');
        $card->setParValue('100.00');
        $card->setBalance('100.00');

        // 获取API数组
        $array = $card->retrieveApiArray();

        // 检查关键字段是否存在
        $this->assertArrayHasKey('cardNumber', $array);
        $this->assertArrayHasKey('parValue', $array);
        $this->assertArrayHasKey('balance', $array);
        $this->assertEquals('CARD123456', $array['cardNumber']);
        $this->assertEquals('100.00', $array['parValue']);
        $this->assertEquals('100.00', $array['balance']);
    }

    public function testRetrieveAdminArray(): void
    {
        $card = $this->createEntity();
        self::assertInstanceOf(Card::class, $card);

        // 设置必要属性
        $card->setCardNumber('CARD123456');
        $card->setParValue('100.00');
        $card->setBalance('100.00');

        // 获取Admin数组
        $array = $card->retrieveAdminArray();

        // 检查是否为数组
        $this->assertArrayHasKey('cardNumber', $array);
        $this->assertArrayHasKey('parValue', $array);
        $this->assertArrayHasKey('balance', $array);
        $this->assertEquals('CARD123456', $array['cardNumber']);
        $this->assertEquals('100.00', $array['parValue']);
        $this->assertEquals('100.00', $array['balance']);
    }
}
