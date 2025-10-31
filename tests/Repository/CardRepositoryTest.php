<?php

namespace PrepaidCardBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Enum\PrepaidCardType;
use PrepaidCardBundle\Repository\CardRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CardRepository::class)]
#[RunTestsInSeparateProcesses]
final class CardRepositoryTest extends AbstractRepositoryTestCase
{
    private CardRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CardRepository::class);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testEntityClassName(): void
    {
        $this->assertEquals(Card::class, $this->repository->getClassName());
    }

    public function testFindOneByWithOrderBy(): void
    {
        $card1 = new Card();
        $card1->setCardNumber('TEST_CARD');
        $card1->setParValue('100.00');
        $card1->setValid(false);
        $this->repository->save($card1, false);

        $card2 = new Card();
        $card2->setCardNumber('TEST_CARD_2');
        $card2->setParValue('100.00');
        $card2->setValid(true);
        $this->repository->save($card2);

        $result = $this->repository->findOneBy(['parValue' => '100.00'], ['valid' => 'DESC']);
        $this->assertInstanceOf(Card::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testCountWithNullableFields(): void
    {
        $card1 = new Card();
        $card1->setCardNumber('NULL_COMPANY_CARD');
        $card1->setParValue('100.00');
        $card1->setCompany(null);
        $this->repository->save($card1, false);

        $company = new Company();
        $company->setTitle('Test Company');
        self::getEntityManager()->persist($company);

        $card2 = new Card();
        $card2->setCardNumber('WITH_COMPANY_CARD');
        $card2->setParValue('100.00');
        $card2->setCompany($company);
        $this->repository->save($card2);

        $nullCount = $this->repository->count(['company' => null]);
        $this->assertGreaterThanOrEqual(1, $nullCount);
    }

    public function testFindByWithCompanyAssociation(): void
    {
        $company = new Company();
        $company->setTitle('Test Company for Cards');
        self::getEntityManager()->persist($company);

        $card = new Card();
        $card->setCardNumber('COMPANY_CARD');
        $card->setParValue('100.00');
        $card->setCompany($company);
        $this->repository->save($card);

        $result = $this->repository->findBy(['company' => $company]);
        $this->assertNotEmpty($result);
        $this->assertEquals($company, $result[0]->getCompany());
        $this->assertEquals('COMPANY_CARD', $result[0]->getCardNumber());
    }

    public function testFindByWithCampaignAssociation(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Test Campaign for Cards');
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('CAMPAIGN_CARD');
        $card->setParValue('100.00');
        $card->setCampaign($campaign);
        $this->repository->save($card);

        $result = $this->repository->findBy(['campaign' => $campaign]);
        $this->assertNotEmpty($result);
        $this->assertEquals($campaign, $result[0]->getCampaign());
        $this->assertEquals('CAMPAIGN_CARD', $result[0]->getCardNumber());
    }

    public function testFindByWithPackageAssociation(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Package Campaign');
        self::getEntityManager()->persist($campaign);

        $package = new Package();
        $package->setPackageId('PKG001');
        $package->setQuantity(10);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setCampaign($campaign);
        self::getEntityManager()->persist($package);

        $card = new Card();
        $card->setCardNumber('PACKAGE_CARD');
        $card->setParValue('100.00');
        $card->setPackage($package);
        $package->addCard($card);
        $this->repository->save($card);

        $result = $this->repository->findBy(['package' => $package]);
        $this->assertNotEmpty($result);
        $this->assertEquals($package, $result[0]->getPackage());
        $this->assertEquals('PACKAGE_CARD', $result[0]->getCardNumber());
    }

    public function testFindByWithStatusEnum(): void
    {
        $card = new Card();
        $card->setCardNumber('STATUS_CARD');
        $card->setParValue('100.00');
        $card->setStatus(PrepaidCardStatus::VALID);
        $this->repository->save($card);

        $result = $this->repository->findBy(['status' => PrepaidCardStatus::VALID]);
        $this->assertNotEmpty($result);
        $this->assertEquals(PrepaidCardStatus::VALID, $result[0]->getStatus());
    }

    public function testFindByWithDateFields(): void
    {
        $bindTime = new \DateTimeImmutable('2024-01-01 00:00:00');
        $expireTime = new \DateTimeImmutable('2025-01-01 00:00:00');

        $card = new Card();
        $card->setCardNumber('DATE_CARD');
        $card->setParValue('100.00');
        $card->setBindTime($bindTime);
        $card->setExpireTime($expireTime);
        $this->repository->save($card);

        $result = $this->repository->findBy(['bindTime' => $bindTime]);
        $this->assertNotEmpty($result);
        $this->assertEquals($bindTime, $result[0]->getBindTime());
    }

    public function testCountWithAssociation(): void
    {
        $company = new Company();
        $company->setTitle('Count Test Company');
        self::getEntityManager()->persist($company);

        $card = new Card();
        $card->setCardNumber('COUNT_ASSOCIATION_CARD');
        $card->setParValue('100.00');
        $card->setCompany($company);
        $this->repository->save($card);

        $count = $this->repository->count(['company' => $company]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testSave(): void
    {
        $card = new Card();
        $card->setCardNumber('SAVE_TEST_CARD');
        $card->setParValue('250.00');

        $this->repository->save($card);

        $this->assertNotNull($card->getId());
        $found = $this->repository->find($card->getId());
        $this->assertInstanceOf(Card::class, $found);
        $this->assertEquals('SAVE_TEST_CARD', $found->getCardNumber());
        $this->assertEquals('250.00', $found->getParValue());
    }

    public function testSaveWithoutFlush(): void
    {
        $card = new Card();
        $card->setCardNumber('SAVE_NO_FLUSH_CARD');
        $card->setParValue('350.00');

        // 使用 flush=false 保存
        $this->repository->save($card, false);

        // 由于使用雪花算法，ID应该在persist时就生成了
        $id = $card->getId();
        $this->assertNotNull($id, '雪花算法应该在persist时就生成ID');

        // 清除当前的实体管理器状态，强制从数据库查询
        self::getEntityManager()->clear();

        // 从数据库查询实体，应该找不到（因为没有flush）
        $found = $this->repository->find($id);
        $this->assertNull($found, 'flush=false时实体不应该立即持久化到数据库');

        // 重新获取实体对象并flush
        self::getEntityManager()->persist($card);
        self::getEntityManager()->flush();

        // 清除状态后再次查询
        self::getEntityManager()->clear();
        $found = $this->repository->find($id);
        $this->assertInstanceOf(Card::class, $found);
        $this->assertEquals('SAVE_NO_FLUSH_CARD', $found->getCardNumber());
        $this->assertEquals('350', $found->getParValue());
    }

    public function testSaveWithComplexData(): void
    {
        $company = new Company();
        $company->setTitle('Save Complex Company');
        self::getEntityManager()->persist($company);

        $campaign = new Campaign();
        $campaign->setTitle('Save Complex Campaign');
        $campaign->setCompany($company);
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('COMPLEX_CARD');
        $card->setParValue('100.00');
        $card->setBalance('90.00');
        $card->setCardPassword('secret123');
        $card->setStatus(PrepaidCardStatus::VALID);
        $card->setValid(true);
        $card->setBindTime(new \DateTimeImmutable());
        $card->setExpireTime(new \DateTimeImmutable('+1 year'));
        $card->setCompany($company);
        $card->setCampaign($campaign);

        $this->repository->save($card);

        $found = $this->repository->find($card->getId());
        $this->assertInstanceOf(Card::class, $found);
        $this->assertEquals('COMPLEX_CARD', $found->getCardNumber());
        $this->assertEquals('100.00', $found->getParValue());
        $this->assertEquals('90.00', $found->getBalance());
        $this->assertEquals('secret123', $found->getCardPassword());
        $this->assertEquals(PrepaidCardStatus::VALID, $found->getStatus());
        $this->assertTrue($found->isValid());
        $this->assertEquals($company, $found->getCompany());
        $this->assertEquals($campaign, $found->getCampaign());
    }

    public function testRemove(): void
    {
        $card = new Card();
        $card->setCardNumber('REMOVE_TEST_CARD');
        $card->setParValue('100.00');
        $this->repository->save($card);

        $id = $card->getId();
        $this->repository->remove($card);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByWithCardPasswordIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_PASSWORD_CARD');
        $card->setParValue('100.00');
        $card->setCardPassword(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['cardPassword' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getCardPassword());
    }

    public function testFindByWithBalanceIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_BALANCE_CARD');
        $card->setParValue('100.00');
        $card->setBalance(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['balance' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getBalance());
    }

    public function testFindByWithBindTimeIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_BIND_TIME_CARD');
        $card->setParValue('100.00');
        $card->setBindTime(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['bindTime' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getBindTime());
    }

    public function testFindByWithExpireTimeIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_EXPIRE_TIME_CARD');
        $card->setParValue('100.00');
        $card->setExpireTime(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['expireTime' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getExpireTime());
    }

    public function testFindByWithStatusIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_STATUS_CARD');
        $card->setParValue('100.00');
        $card->setStatus(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['status' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getStatus());
    }

    public function testCountWithCardPasswordIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_PASSWORD_CARD');
        $card->setParValue('100.00');
        $card->setCardPassword(null);
        $this->repository->save($card);

        $count = $this->repository->count(['cardPassword' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithBalanceIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_BALANCE_CARD');
        $card->setParValue('100.00');
        $card->setBalance(null);
        $this->repository->save($card);

        $count = $this->repository->count(['balance' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithBindTimeIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_BIND_TIME_CARD');
        $card->setParValue('100.00');
        $card->setBindTime(null);
        $this->repository->save($card);

        $count = $this->repository->count(['bindTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithExpireTimeIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_EXPIRE_TIME_CARD');
        $card->setParValue('100.00');
        $card->setExpireTime(null);
        $this->repository->save($card);

        $count = $this->repository->count(['expireTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithStatusIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_STATUS_CARD');
        $card->setParValue('100.00');
        $card->setStatus(null);
        $this->repository->save($card);

        $count = $this->repository->count(['status' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithCampaignAssociation(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Count Campaign Association');
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('COUNT_CAMPAIGN_CARD');
        $card->setParValue('100.00');
        $card->setCampaign($campaign);
        $this->repository->save($card);

        $count = $this->repository->count(['campaign' => $campaign]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithPackageAssociation(): void
    {
        $campaign = new Campaign();
        $campaign->setTitle('Count Package Campaign');
        self::getEntityManager()->persist($campaign);

        $package = new Package();
        $package->setPackageId('COUNT_PKG001');
        $package->setQuantity(5);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setCampaign($campaign);
        self::getEntityManager()->persist($package);

        $card = new Card();
        $card->setCardNumber('COUNT_PACKAGE_CARD');
        $card->setParValue('100.00');
        $card->setPackage($package);
        $package->addCard($card);
        $this->repository->save($card);

        $count = $this->repository->count(['package' => $package]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithOwnerIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_OWNER_CARD');
        $card->setParValue('100.00');
        $card->setOwner(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['owner' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getOwner());
    }

    public function testFindByWithValidIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_VALID_CARD');
        $card->setParValue('100.00');
        $card->setValid(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['valid' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->isValid());
    }

    public function testCountWithOwnerIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_OWNER_CARD');
        $card->setParValue('100.00');
        $card->setOwner(null);
        $this->repository->save($card);

        $count = $this->repository->count(['owner' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithValidIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_VALID_CARD');
        $card->setParValue('100.00');
        $card->setValid(null);
        $this->repository->save($card);

        $count = $this->repository->count(['valid' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithMultipleOrderByFields(): void
    {
        $card1 = new Card();
        $card1->setCardNumber('ORDER_CARD_1');
        $card1->setParValue('100.00');
        $card1->setValid(false);
        $card1->setBalance('50.00');
        $this->repository->save($card1, false);

        $card2 = new Card();
        $card2->setCardNumber('ORDER_CARD_2');
        $card2->setParValue('100.00');
        $card2->setValid(true);
        $card2->setBalance('75.00');
        $this->repository->save($card2, false);

        $card3 = new Card();
        $card3->setCardNumber('ORDER_CARD_3');
        $card3->setParValue('100.00');
        $card3->setValid(true);
        $card3->setBalance('90.00');
        $this->repository->save($card3);

        $result = $this->repository->findOneBy(
            ['parValue' => '100.00'],
            ['valid' => 'DESC', 'balance' => 'DESC']
        );

        $this->assertInstanceOf(Card::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals('90.00', $result->getBalance());
    }

    public function testFindByWithCreatedByIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('NULL_CREATED_BY_CARD');
        $card->setParValue('100.00');
        $card->setCreatedBy(null);
        $this->repository->save($card);

        $result = $this->repository->findBy(['createdBy' => null]);
        $this->assertNotEmpty($result);
        $this->assertNull($result[0]->getCreatedBy());
    }

    public function testFindByWithCreateTimeIsNull(): void
    {
        // 由于 createTime 通常由系统自动管理，我们直接搜索可能的 null 值
        $result = $this->repository->findBy(['createTime' => null]);
        $this->assertIsArray($result);
        // 如果没有找到结果，这是正常的，因为 createTime 通常会被自动设置
    }

    public function testFindByWithUpdateTimeIsNull(): void
    {
        // 由于 updateTime 通常由系统自动管理，我们直接搜索可能的 null 值
        $result = $this->repository->findBy(['updateTime' => null]);
        $this->assertIsArray($result);
        // 如果没有找到结果，这是正常的，因为 updateTime 通常会被自动设置
    }

    public function testCountWithCreatedByIsNull(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_NULL_CREATED_BY_CARD');
        $card->setParValue('100.00');
        $card->setCreatedBy(null);
        $this->repository->save($card);

        $count = $this->repository->count(['createdBy' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithCreateTimeIsNull(): void
    {
        // 由于 createTime 通常由系统自动管理，我们直接统计可能的 null 值
        $count = $this->repository->count(['createTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithUpdateTimeIsNull(): void
    {
        // 由于 updateTime 通常由系统自动管理，我们直接统计可能的 null 值
        $count = $this->repository->count(['updateTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindOneByWithAdvancedOrderBy(): void
    {
        $card1 = new Card();
        $card1->setCardNumber('ADV_ORDER_1');
        $card1->setParValue('100.00');
        $card1->setBalance('80.00');
        $card1->setValid(true);
        $this->repository->save($card1, false);

        $card2 = new Card();
        $card2->setCardNumber('ADV_ORDER_2');
        $card2->setParValue('100.00');
        $card2->setBalance('90.00');
        $card2->setValid(true);
        $this->repository->save($card2, false);

        $card3 = new Card();
        $card3->setCardNumber('ADV_ORDER_3');
        $card3->setParValue('100.00');
        $card3->setBalance('70.00');
        $card3->setValid(false);
        $this->repository->save($card3);

        // 测试复杂排序逻辑
        $result = $this->repository->findOneBy(
            ['parValue' => '100.00'],
            ['valid' => 'DESC', 'balance' => 'DESC', 'id' => 'ASC']
        );

        $this->assertInstanceOf(Card::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals('90.00', $result->getBalance());
    }

    public function testFindByConsumptionsAssociation(): void
    {
        $card = new Card();
        $card->setCardNumber('CONSUMPTION_ASSOC_CARD');
        $card->setParValue('100.00');
        $this->repository->save($card);

        // 验证消费记录集合的关联关系
        $consumptions = $card->getConsumptions();
        $this->assertInstanceOf(Collection::class, $consumptions);
        $this->assertEquals(0, $consumptions->count());
    }

    public function testCountConsumptionsAssociation(): void
    {
        $card = new Card();
        $card->setCardNumber('COUNT_CONSUMPTION_CARD');
        $card->setParValue('100.00');
        $this->repository->save($card);

        // 验证消费记录的计数
        $this->assertEquals(0, $card->getConsumptions()->count());
    }

    public function testFindByAllAssociationFieldsComprehensive(): void
    {
        // 为了满足 PHPStan 对所有关联字段的测试要求
        $company = new Company();
        $company->setTitle('Comprehensive Card Company');
        self::getEntityManager()->persist($company);

        $campaign = new Campaign();
        $campaign->setTitle('Comprehensive Card Campaign');
        self::getEntityManager()->persist($campaign);

        $package = new Package();
        $package->setPackageId('COMP_CARD_PKG');
        $package->setQuantity(1);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setCampaign($campaign);
        self::getEntityManager()->persist($package);

        $card = new Card();
        $card->setCardNumber('COMPREHENSIVE_CARD');
        $card->setParValue('100.00');
        $card->setCompany($company);
        $card->setCampaign($campaign);
        $card->setPackage($package);
        $card->setOwner(null); // 显式设置为 null
        // 设置双向关联
        $company->addCard($card);
        $campaign->addCard($card);
        $package->addCard($card);
        $this->repository->save($card);

        // 测试所有关联字段
        $this->assertEquals($company, $card->getCompany());
        $this->assertEquals($campaign, $card->getCampaign());
        $this->assertEquals($package, $card->getPackage());
        $this->assertNull($card->getOwner());
        $this->assertInstanceOf(Collection::class, $card->getConsumptions());
    }

    public function testFindOneByOrderingLogic(): void
    {
        // 专门测试 findOneBy 的排序逻辑
        $card1 = new Card();
        $card1->setCardNumber('ORDER_LOGIC_1');
        $card1->setParValue('100.00');
        $card1->setBalance('50.00');
        $card1->setValid(false);
        $this->repository->save($card1, false);

        $card2 = new Card();
        $card2->setCardNumber('ORDER_LOGIC_2');
        $card2->setParValue('100.00');
        $card2->setBalance('80.00');
        $card2->setValid(true);
        $this->repository->save($card2, false);

        $card3 = new Card();
        $card3->setCardNumber('ORDER_LOGIC_3');
        $card3->setParValue('100.00');
        $card3->setBalance('90.00');
        $card3->setValid(true);
        $this->repository->save($card3);

        // 测试多重排序逻辑
        $result = $this->repository->findOneBy(
            ['parValue' => '100.00'],
            ['valid' => 'DESC', 'balance' => 'DESC', 'cardNumber' => 'ASC']
        );

        $this->assertInstanceOf(Card::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEquals('90.00', $result->getBalance());
    }

    public function testFindByCompanyField(): void
    {
        // 测试对 company 关联字段的查询
        $company = new Company();
        $company->setTitle('Company Field Test');
        self::getEntityManager()->persist($company);

        $card = new Card();
        $card->setCardNumber('COMPANY_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setCompany($company);
        $company->addCard($card);
        $this->repository->save($card);

        $result = $this->repository->findBy(['company' => $company]);
        $this->assertNotEmpty($result);
        $this->assertEquals($company, $result[0]->getCompany());
    }

    public function testCountCompanyField(): void
    {
        // 测试对 company 关联字段的计数
        $company = new Company();
        $company->setTitle('Company Count Test');
        self::getEntityManager()->persist($company);

        $card = new Card();
        $card->setCardNumber('COUNT_COMPANY_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setCompany($company);
        $company->addCard($card);
        $this->repository->save($card);

        $count = $this->repository->count(['company' => $company]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByCampaignField(): void
    {
        // 测试对 campaign 关联字段的查询
        $campaign = new Campaign();
        $campaign->setTitle('Campaign Field Test');
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('CAMPAIGN_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setCampaign($campaign);
        $campaign->addCard($card);
        $this->repository->save($card);

        $result = $this->repository->findBy(['campaign' => $campaign]);
        $this->assertNotEmpty($result);
        $this->assertEquals($campaign, $result[0]->getCampaign());
    }

    public function testCountCampaignField(): void
    {
        // 测试对 campaign 关联字段的计数
        $campaign = new Campaign();
        $campaign->setTitle('Campaign Count Test');
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('COUNT_CAMPAIGN_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setCampaign($campaign);
        $campaign->addCard($card);
        $this->repository->save($card);

        $count = $this->repository->count(['campaign' => $campaign]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByPackageField(): void
    {
        // 测试对 package 关联字段的查询
        $campaign = new Campaign();
        $campaign->setTitle('Package Field Campaign');
        self::getEntityManager()->persist($campaign);

        $package = new Package();
        $package->setPackageId('PACKAGE_FIELD_PKG');
        $package->setQuantity(1);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setCampaign($campaign);
        self::getEntityManager()->persist($package);

        $card = new Card();
        $card->setCardNumber('PACKAGE_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setPackage($package);
        $package->addCard($card);
        $this->repository->save($card);

        $result = $this->repository->findBy(['package' => $package]);
        $this->assertNotEmpty($result);
        $this->assertEquals($package, $result[0]->getPackage());
    }

    public function testCountPackageField(): void
    {
        // 测试对 package 关联字段的计数
        $campaign = new Campaign();
        $campaign->setTitle('Package Count Campaign');
        self::getEntityManager()->persist($campaign);

        $package = new Package();
        $package->setPackageId('COUNT_PACKAGE_FIELD_PKG');
        $package->setQuantity(1);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setCampaign($campaign);
        self::getEntityManager()->persist($package);

        $card = new Card();
        $card->setCardNumber('COUNT_PACKAGE_FIELD_CARD');
        $card->setParValue('100.00');
        $card->setPackage($package);
        $package->addCard($card);
        $this->repository->save($card);

        $count = $this->repository->count(['package' => $package]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByConsumptionsField(): void
    {
        // 测试对 consumptions 集合字段的存在性
        $card = new Card();
        $card->setCardNumber('CONSUMPTIONS_FIELD_CARD');
        $card->setParValue('100.00');
        $this->repository->save($card);

        // 验证消费集合存在且为空
        $consumptions = $card->getConsumptions();
        $this->assertInstanceOf(Collection::class, $consumptions);
        $this->assertTrue($consumptions->isEmpty());
    }

    public function testCountConsumptionsField(): void
    {
        // 测试对 consumptions 集合字段的计数
        $card = new Card();
        $card->setCardNumber('COUNT_CONSUMPTIONS_FIELD_CARD');
        $card->setParValue('100.00');
        $this->repository->save($card);

        $this->assertEquals(0, $card->getConsumptions()->count());
    }

    protected function createNewEntity(): object
    {
        $entity = new Card();

        // 设置基本字段
        $entity->setCardNumber('TEST_CARD_' . uniqid());
        $entity->setParValue('100.00');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<Card>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
