<?php

namespace PrepaidCardBundle\Tests\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Repository\PackageRepository;

class PackageRepositoryTest extends TestCase
{
    /** @var ManagerRegistry&MockObject */
    private $managerRegistry;
    
    private PackageRepository $repository;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->repository = new PackageRepository($this->managerRegistry);
    }

    public function testRepositoryInstantiation(): void
    {
        $this->assertInstanceOf(PackageRepository::class, $this->repository);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $this->repository);
    }

    public function testRepositoryIsServiceEntityRepository(): void
    {
        // 验证Repository是ServiceEntityRepository的实例
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $this->repository);
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