<?php

namespace PrepaidCardBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Entity\Package;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;
use PrepaidCardBundle\Repository\PackageRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(PackageRepository::class)]
#[RunTestsInSeparateProcesses]
final class PackageRepositoryTest extends AbstractRepositoryTestCase
{
    private PackageRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(PackageRepository::class);
    }

    protected function createNewEntity(): object
    {
        $entity = new Package();

        // 设置基本字段
        $entity->setPackageId('TEST_PKG_' . uniqid());
        $entity->setQuantity(10);
        $entity->setType(PrepaidCardType::ONE_TIME);
        $entity->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $entity->setExpireNum(30);
        $entity->setValid(true);

        // 创建必需的 campaign 关联
        $campaign = $this->createTestCampaign('Test Campaign ' . uniqid());
        $entity->setCampaign($campaign);

        return $entity;
    }

    protected function getRepository(): PackageRepository
    {
        return $this->repository;
    }

    #[Test]
    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $campaign = $this->createTestCampaign('Test Campaign');
        $this->createTestPackage('PKG001', 300, $campaign);
        $this->createTestPackage('PKG002', 100, $campaign);
        $this->createTestPackage('PKG003', 200, $campaign);

        $found = $this->repository->findOneBy(
            ['campaign' => $campaign],
            ['quantity' => 'ASC']
        );

        $this->assertInstanceOf(Package::class, $found);
        $this->assertEquals(100, $found->getQuantity());
    }

    #[Test]
    public function testSaveWithNewEntityShouldPersistToDatabase(): void
    {
        $campaign = $this->createTestCampaign('Test Campaign');
        $package = new Package();
        $package->setPackageId('PKG_SAVE_TEST');
        $package->setQuantity(150);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setExpireNum(30);
        $package->setCampaign($campaign);

        $this->repository->save($package);

        $found = $this->repository->findOneBy(['packageId' => 'PKG_SAVE_TEST']);
        $this->assertInstanceOf(Package::class, $found);
        $this->assertEquals('PKG_SAVE_TEST', $found->getPackageId());
        $this->assertEquals(150, $found->getQuantity());
    }

    #[Test]
    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        $campaign = $this->createTestCampaign('Test Campaign');
        $package = new Package();
        $package->setPackageId('PKG_NO_FLUSH');
        $package->setQuantity(150);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setExpireNum(30);
        $package->setCampaign($campaign);

        $this->repository->save($package, false);

        // Without flush, entity should not be found
        $found = $this->repository->findOneBy(['packageId' => 'PKG_NO_FLUSH']);
        $this->assertNull($found);

        // After manual flush, should be found
        self::getEntityManager()->flush();
        $found = $this->repository->findOneBy(['packageId' => 'PKG_NO_FLUSH']);
        $this->assertInstanceOf(Package::class, $found);
    }

    #[Test]
    public function testRemoveShouldDeleteEntityFromDatabase(): void
    {
        $package = $this->createTestPackage('PKG_REMOVE_TEST', 100);
        $packageId = $package->getId();

        $this->repository->remove($package);

        $found = $this->repository->find($packageId);
        $this->assertNull($found);
    }

    #[Test]
    public function testFindByWithCampaignAssociationShouldReturnCorrectResults(): void
    {
        $campaign1 = $this->createTestCampaign('Campaign 1');
        $campaign2 = $this->createTestCampaign('Campaign 2');

        $package1 = $this->createTestPackage('PKG001', 100, $campaign1);
        $package2 = $this->createTestPackage('PKG002', 200, $campaign1);
        $package3 = $this->createTestPackage('PKG003', 300, $campaign2);

        $results = $this->repository->findBy(['campaign' => $campaign1]);

        $this->assertCount(2, $results);
        $packageIds = array_map(fn (Package $p) => $p->getPackageId(), $results);
        $this->assertContains('PKG001', $packageIds);
        $this->assertContains('PKG002', $packageIds);
        $this->assertNotContains('PKG003', $packageIds);
    }

    #[Test]
    public function testFindByWithSpecificCampaignShouldReturnOnlyMatchingPackages(): void
    {
        $campaign1 = $this->createTestCampaign('Test Campaign 1');
        $campaign2 = $this->createTestCampaign('Test Campaign 2');
        $this->createTestPackage('PKG001', 100, $campaign1);
        $packageWithDifferentCampaign = $this->createTestPackage('PKG002', 200, $campaign2);

        $results = $this->repository->findBy(['campaign' => $campaign2]);

        $this->assertCount(1, $results);
        $packageIds = array_map(fn (Package $p) => $p->getPackageId(), $results);
        $this->assertContains('PKG002', $packageIds);
        $this->assertNotContains('PKG001', $packageIds);
    }

    #[Test]
    public function testFindByWithValidFlagShouldFilterCorrectly(): void
    {
        $package1 = $this->createTestPackage('PKG001', 100);
        $package1->setValid(true);
        $this->repository->save($package1);

        $package2 = $this->createTestPackage('PKG002', 200);
        $package2->setValid(false);
        $this->repository->save($package2);

        $validPackages = $this->repository->findBy(['valid' => true]);
        $invalidPackages = $this->repository->findBy(['valid' => false]);

        $validIds = array_map(fn (Package $p) => $p->getPackageId(), $validPackages);
        $invalidIds = array_map(fn (Package $p) => $p->getPackageId(), $invalidPackages);

        $this->assertContains('PKG001', $validIds);
        $this->assertContains('PKG002', $invalidIds);
    }

    #[Test]
    public function testFindByWithTypeEnumShouldFilterCorrectly(): void
    {
        $package1 = $this->createTestPackage('PKG001', 100);
        $package1->setType(PrepaidCardType::ONE_TIME);
        $this->repository->save($package1);

        $package2 = $this->createTestPackage('PKG002', 200);
        $package2->setType(PrepaidCardType::AFTER);
        $this->repository->save($package2);

        $oneTimePackages = $this->repository->findBy(['type' => PrepaidCardType::ONE_TIME]);
        $afterPackages = $this->repository->findBy(['type' => PrepaidCardType::AFTER]);

        $oneTimeIds = array_map(fn (Package $p) => $p->getPackageId(), $oneTimePackages);
        $afterIds = array_map(fn (Package $p) => $p->getPackageId(), $afterPackages);

        $this->assertContains('PKG001', $oneTimeIds);
        $this->assertContains('PKG002', $afterIds);
    }

    #[Test]
    public function testCountByAssociationCampaignShouldReturnCorrectNumber(): void
    {
        $campaign = $this->createTestCampaign('Test Campaign');
        $this->createTestPackage('PKG001', 100, $campaign);
        $this->createTestPackage('PKG002', 200, $campaign);
        $this->createTestPackage('PKG003', 300, $campaign);
        $this->createTestPackage('PKG004', 400, $campaign);

        $otherCampaign = $this->createTestCampaign('Other Campaign');
        $this->createTestPackage('PKG005', 500, $otherCampaign);
        $this->createTestPackage('PKG006', 600, $otherCampaign);

        $count = $this->repository->count(['campaign' => $campaign]);

        $this->assertSame(4, $count);
    }

    #[Test]
    public function testFindOneByAssociationCampaignShouldReturnMatchingEntity(): void
    {
        $campaign = $this->createTestCampaign('Test Campaign');
        $package = $this->createTestPackage('PKG001', 100, $campaign);

        $otherCampaign = $this->createTestCampaign('Other Campaign');
        $this->createTestPackage('PKG002', 200, $otherCampaign);

        $found = $this->repository->findOneBy(['campaign' => $campaign]);

        $this->assertInstanceOf(Package::class, $found);
        $this->assertEquals($package->getId(), $found->getId());
        $this->assertNotNull($found->getCampaign());
        $this->assertEquals($campaign->getId(), $found->getCampaign()->getId());
    }

    private function createTestCampaign(string $title): Campaign
    {
        $company = $this->createTestCompany('Test Company ' . uniqid());

        $campaign = new Campaign();
        $campaign->setTitle($title);
        $campaign->setCompany($company);
        $campaign->setValid(true);

        self::getEntityManager()->persist($campaign);
        self::getEntityManager()->flush();

        return $campaign;
    }

    private function createTestCompany(string $title): Company
    {
        $company = new Company();
        $company->setTitle($title);

        self::getEntityManager()->persist($company);
        self::getEntityManager()->flush();

        return $company;
    }

    private function createTestPackage(string $packageId, int $quantity, ?Campaign $campaign = null): Package
    {
        if (null === $campaign) {
            $campaign = $this->createTestCampaign('Default Campaign ' . uniqid());
        }

        $package = new Package();
        $package->setPackageId($packageId);
        $package->setQuantity($quantity);
        $package->setType(PrepaidCardType::ONE_TIME);
        $package->setExpireType(PrepaidCardExpireType::SAME_WITH_CARD);
        $package->setExpireNum(30);
        $package->setCampaign($campaign);
        $package->setValid(true);

        self::getEntityManager()->persist($package);
        self::getEntityManager()->flush();

        return $package;
    }
}
