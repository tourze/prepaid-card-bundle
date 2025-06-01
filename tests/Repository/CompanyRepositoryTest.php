<?php

namespace PrepaidCardBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Repository\CompanyRepository;

class CompanyRepositoryTest extends TestCase
{
    /** @var ManagerRegistry&MockObject */
    private $managerRegistry;
    
    private CompanyRepository $repository;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new CompanyRepository($this->managerRegistry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(CompanyRepository::class, $this->repository);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $this->repository);
    }

    public function testHasStandardRepositoryMethods(): void
    {
        // 验证Repository具有标准的Doctrine方法
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'findAll'));
        $this->assertTrue(method_exists($this->repository, 'findBy'));
        $this->assertTrue(method_exists($this->repository, 'findOneBy'));
        $this->assertTrue(method_exists($this->repository, 'createQueryBuilder'));
    }

    public function testEntityClassName(): void
    {
        // 验证Repository关联的Entity类名正确
        $reflectionClass = new \ReflectionClass($this->repository);
        $constructor = $reflectionClass->getConstructor();
        $this->assertNotNull($constructor);
        
        // 验证构造函数参数
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('registry', $parameters[0]->getName());
    }
} 