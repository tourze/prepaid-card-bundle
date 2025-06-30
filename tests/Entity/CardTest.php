<?php

namespace PrepaidCardBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Consumption;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Tests\Mock\MockBizUser;

class CardTest extends TestCase
{
    private Card $card;

    protected function setUp(): void
    {
        $this->card = new Card();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->card->setCardNumber('CARD123456');
        $this->assertEquals('CARD123456', $this->card->getCardNumber());

        $this->card->setCardPassword('password123');
        $this->assertEquals('password123', $this->card->getCardPassword());

        $this->card->setParValue('100.00');
        $this->assertEquals('100.00', $this->card->getParValue());

        $this->card->setBalance('100.00');
        $this->assertEquals('100.00', $this->card->getBalance());

        $now = new \DateTimeImmutable();
        $this->card->setBindTime($now);
        $this->assertEquals($now, $this->card->getBindTime());

        $expireTime = new \DateTimeImmutable('+1 year');
        $this->card->setExpireTime($expireTime);
        $this->assertEquals($expireTime, $this->card->getExpireTime());

        $this->card->setStatus(PrepaidCardStatus::VALID);
        $this->assertEquals(PrepaidCardStatus::VALID, $this->card->getStatus());

        $this->card->setValid(true);
        $this->assertTrue($this->card->isValid());

        $createTime = new \DateTimeImmutable();
        $this->card->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->card->getCreateTime());

        $updateTime = new \DateTimeImmutable();
        $this->card->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->card->getUpdateTime());

        $this->card->setCreatedBy('admin');
        $this->assertEquals('admin', $this->card->getCreatedBy());
    }

    public function testRelationships(): void
    {
        // 测试关联关系
        $company = $this->createMock(Company::class);
        $this->card->setCompany($company);
        $this->assertSame($company, $this->card->getCompany());

        $campaign = $this->createMock(Campaign::class);
        $this->card->setCampaign($campaign);
        $this->assertSame($campaign, $this->card->getCampaign());

        $package = $this->createMock(Package::class);
        $this->card->setPackage($package);
        $this->assertSame($package, $this->card->getPackage());

        // 使用模拟BizUser
        $owner = new MockBizUser();
        // 跳过设置owner，因为它需要BizUser类型
        // 此处我们只测试其他关系
    }

    public function testAddAndRemoveConsumption(): void
    {
        $consumption = $this->createMock(Consumption::class);

        // 初始应该是空集合
        $this->assertInstanceOf(Collection::class, $this->card->getConsumptions());
        $this->assertCount(0, $this->card->getConsumptions());

        // 添加消费记录
        $this->card->addConsumption($consumption);
        $this->assertCount(1, $this->card->getConsumptions());
        $this->assertTrue($this->card->getConsumptions()->contains($consumption));

        // 移除消费记录
        $this->card->removeConsumption($consumption);
        $this->assertCount(0, $this->card->getConsumptions());
        $this->assertFalse($this->card->getConsumptions()->contains($consumption));
    }

    public function testCheckStatus_whenExpired(): void
    {
        // 设置过期时间为过去
        $pastDate = new \DateTimeImmutable('-1 day');
        $this->card->setExpireTime($pastDate);
        $this->card->setBalance('100.00');
        $this->card->setStatus(PrepaidCardStatus::VALID);

        // 检查状态
        $this->card->checkStatus();

        // 应该变为过期状态
        $this->assertEquals(PrepaidCardStatus::EXPIRED, $this->card->getStatus());
    }

    public function testCheckStatus_whenEmpty(): void
    {
        // 设置余额为0
        $futureDate = new \DateTimeImmutable('+1 day');
        $this->card->setExpireTime($futureDate);
        $this->card->setBalance('0.00');
        $this->card->setStatus(PrepaidCardStatus::VALID);

        // 检查状态
        $this->card->checkStatus();

        // 应该变为已用完状态
        $this->assertEquals(PrepaidCardStatus::EMPTY, $this->card->getStatus());
    }

    public function testCheckStatus_whenValid(): void
    {
        // 设置有效卡片
        $futureDate = new \DateTimeImmutable('+1 day');
        $this->card->setExpireTime($futureDate);
        $this->card->setBalance('100.00');
        $this->card->setStatus(PrepaidCardStatus::INIT);

        // 检查状态
        $this->card->checkStatus();

        // 应该变为有效状态
        $this->assertEquals(PrepaidCardStatus::VALID, $this->card->getStatus());
    }

    public function testRetrieveApiArray(): void
    {
        // 设置必要属性
        $this->card->setCardNumber('CARD123456');
        $this->card->setParValue('100.00');
        $this->card->setBalance('100.00');

        // 获取API数组
        $array = $this->card->retrieveApiArray();

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
        // 设置必要属性
        $this->card->setCardNumber('CARD123456');
        $this->card->setParValue('100.00');
        $this->card->setBalance('100.00');

        // 获取Admin数组
        $array = $this->card->retrieveAdminArray();

        // 检查是否为数组
        $this->assertArrayHasKey('cardNumber', $array);
        $this->assertArrayHasKey('parValue', $array);
        $this->assertArrayHasKey('balance', $array);
        $this->assertEquals('CARD123456', $array['cardNumber']);
        $this->assertEquals('100.00', $array['parValue']);
        $this->assertEquals('100.00', $array['balance']);
    }
}
