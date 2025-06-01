<?php

namespace PrepaidCardBundle\Tests;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\PrepaidCardBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\Symfony\CronJob\CronJobBundle;

class PrepaidCardBundleTest extends TestCase
{
    private PrepaidCardBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new PrepaidCardBundle();
    }

    public function testBundleInstantiation(): void
    {
        $this->assertInstanceOf(PrepaidCardBundle::class, $this->bundle);
    }

    public function testExtendsBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testImplementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = PrepaidCardBundle::getBundleDependencies();
        
        $this->assertIsArray($dependencies);
        $this->assertCount(2, $dependencies);
        
        // 验证依赖包
        $this->assertArrayHasKey(DoctrineIndexedBundle::class, $dependencies);
        $this->assertArrayHasKey(CronJobBundle::class, $dependencies);
        
        // 验证依赖配置
        $this->assertEquals(['all' => true], $dependencies[DoctrineIndexedBundle::class]);
        $this->assertEquals(['all' => true], $dependencies[CronJobBundle::class]);
    }

    public function testBundleDependenciesFormat(): void
    {
        $dependencies = PrepaidCardBundle::getBundleDependencies();
        
        foreach ($dependencies as $bundleClass => $config) {
            // 验证Bundle类存在
            $this->assertTrue(class_exists($bundleClass), "Bundle class {$bundleClass} should exist");
            
            // 验证配置格式
            $this->assertIsArray($config);
            $this->assertArrayHasKey('all', $config);
            $this->assertTrue($config['all']);
        }
    }

    public function testHasPermissionAttribute(): void
    {
        $reflection = new \ReflectionClass(PrepaidCardBundle::class);
        $attributes = $reflection->getAttributes();
        
        $this->assertNotEmpty($attributes, 'Bundle should have attributes');
        
        // 查找AsPermission属性
        $hasPermissionAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'AsPermission')) {
                $hasPermissionAttribute = true;
                break;
            }
        }
        
        $this->assertTrue($hasPermissionAttribute, 'Bundle should have AsPermission attribute');
    }
} 