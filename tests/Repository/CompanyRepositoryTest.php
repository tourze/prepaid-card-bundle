<?php

namespace PrepaidCardBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Entity\Campaign;
use PrepaidCardBundle\Entity\Card;
use PrepaidCardBundle\Entity\Company;
use PrepaidCardBundle\Repository\CompanyRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CompanyRepository::class)]
#[RunTestsInSeparateProcesses]
final class CompanyRepositoryTest extends AbstractRepositoryTestCase
{
    private CompanyRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CompanyRepository::class);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testEntityClassName(): void
    {
        $this->assertEquals(Company::class, $this->repository->getClassName());
    }

    public function testFindOneByWithOrderBy(): void
    {
        $suffix = uniqid();
        $company1 = new Company();
        $company1->setTitle("First Company {$suffix}");
        $this->repository->save($company1, false);

        $company2 = new Company();
        $company2->setTitle("Second Company {$suffix}");
        $this->repository->save($company2);

        $result = $this->repository->findOneBy([], ['title' => 'DESC']);
        $this->assertInstanceOf(Company::class, $result);
        $this->assertNotNull($result->getTitle());
    }

    public function testSave(): void
    {
        $company = new Company();
        $company->setTitle('Save Test Company ' . uniqid());

        $this->repository->save($company);

        $this->assertNotNull($company->getId());
        $found = $this->repository->find($company->getId());
        $this->assertInstanceOf(Company::class, $found);
        $this->assertEquals($company->getTitle(), $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $company = new Company();
        $company->setTitle('Save No Flush Company ' . uniqid());

        // 使用 flush=false 保存
        $this->repository->save($company, false);

        // 由于使用雪花算法，ID应该在persist时就生成了
        $id = $company->getId();
        $this->assertNotNull($id, '雪花算法应该在persist时就生成ID');

        // 清除当前的实体管理器状态，强制从数据库查询
        self::getEntityManager()->clear();

        // 从数据库查询实体，应该找不到（因为没有flush）
        $found = $this->repository->find($id);
        $this->assertNull($found, 'flush=false时实体不应该立即持久化到数据库');

        // 重新获取实体对象并flush
        self::getEntityManager()->persist($company);
        self::getEntityManager()->flush();

        // 清除状态后再次查询
        self::getEntityManager()->clear();
        $found = $this->repository->find($id);
        $this->assertInstanceOf(Company::class, $found);
        $this->assertEquals($company->getTitle(), $found->getTitle());
    }

    public function testRemove(): void
    {
        $company = new Company();
        $company->setTitle('Remove Test Company ' . uniqid());
        $this->repository->save($company);

        $id = $company->getId();
        $this->repository->remove($company);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByWithCreatedByIsNull(): void
    {
        $company = new Company();
        $company->setTitle('Null Created By Company ' . uniqid());
        $company->setCreatedBy(null);
        $this->repository->save($company);

        $result = $this->repository->findBy(['createdBy' => null]);
        $this->assertNotEmpty($result);
        $found = false;
        foreach ($result as $item) {
            if ($item->getTitle() === $company->getTitle()) {
                $this->assertNull($item->getCreatedBy());
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Company with null createdBy was not found');
    }

    public function testFindByWithUpdatedByIsNull(): void
    {
        $company = new Company();
        $company->setTitle('Null Updated By Company ' . uniqid());
        $company->setUpdatedBy(null);
        $this->repository->save($company);

        $result = $this->repository->findBy(['updatedBy' => null]);
        $this->assertNotEmpty($result);
        $found = false;
        foreach ($result as $item) {
            if ($item->getTitle() === $company->getTitle()) {
                $this->assertNull($item->getUpdatedBy());
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Company with null updatedBy was not found');
    }

    public function testCountWithCreatedByIsNull(): void
    {
        $company = new Company();
        $company->setTitle('Count Null Created By Company ' . uniqid());
        $company->setCreatedBy(null);
        $this->repository->save($company);

        $count = $this->repository->count(['createdBy' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithUpdatedByIsNull(): void
    {
        $company = new Company();
        $company->setTitle('Count Null Updated By Company ' . uniqid());
        $company->setUpdatedBy(null);
        $this->repository->save($company);

        $count = $this->repository->count(['updatedBy' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithCreateTimeIsNull(): void
    {
        $result = $this->repository->findBy(['createTime' => null]);
        $this->assertIsArray($result);
    }

    public function testFindByWithUpdateTimeIsNull(): void
    {
        $result = $this->repository->findBy(['updateTime' => null]);
        $this->assertIsArray($result);
    }

    public function testCountWithCreateTimeIsNull(): void
    {
        $count = $this->repository->count(['createTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithUpdateTimeIsNull(): void
    {
        $count = $this->repository->count(['updateTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindOneByWithMultipleOrderByFields(): void
    {
        $suffix = uniqid();
        $company1 = new Company();
        $company1->setTitle("A Company {$suffix}");
        $this->repository->save($company1, false);

        $company2 = new Company();
        $company2->setTitle("B Company {$suffix}");
        $this->repository->save($company2, false);

        $company3 = new Company();
        $company3->setTitle("C Company {$suffix}");
        $this->repository->save($company3);

        $result = $this->repository->findOneBy(
            [],
            ['title' => 'DESC', 'id' => 'ASC']
        );

        $this->assertInstanceOf(Company::class, $result);
        $this->assertNotNull($result->getTitle());
    }

    public function testSaveWithComplexData(): void
    {
        $company = new Company();
        $company->setTitle('Complex Company ' . uniqid());

        $this->repository->save($company, false);

        $campaign = new Campaign();
        $campaign->setTitle('Complex Campaign ' . uniqid());
        $campaign->setCompany($company);
        self::getEntityManager()->persist($campaign);

        $card = new Card();
        $card->setCardNumber('COMPLEX' . uniqid());
        $card->setParValue('100.00');
        $card->setCompany($company);
        self::getEntityManager()->persist($card);

        self::getEntityManager()->flush();

        // Clear entity manager to force reloading from database
        self::getEntityManager()->clear();

        $found = $this->repository->find($company->getId());
        $this->assertInstanceOf(Company::class, $found);
        $this->assertEquals($company->getTitle(), $found->getTitle());
        $this->assertCount(1, $found->getCampaigns());
        $this->assertCount(1, $found->getCards());
    }

    protected function createNewEntity(): object
    {
        $entity = new Company();

        // 设置基本字段
        $entity->setTitle('Test Company ' . uniqid());

        return $entity;
    }

    protected function getRepository(): CompanyRepository
    {
        return $this->repository;
    }
}
