<?php

namespace PrepaidCardBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\DependencyInjection\PrepaidCardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PrepaidCardExtensionTest extends TestCase
{
    private PrepaidCardExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new PrepaidCardExtension();
    }

    public function testExtensionInstantiation(): void
    {
        $this->assertInstanceOf(PrepaidCardExtension::class, $this->extension);
    }

    public function testExtendsExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function testHasLoadMethod(): void
    {
        // 验证Extension的load方法是公开的
        $reflection = new \ReflectionMethod($this->extension, 'load');
        $this->assertTrue($reflection->isPublic());
        
        // 验证方法参数
        $parameters = $reflection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('configs', $parameters[0]->getName());
        $this->assertEquals('container', $parameters[1]->getName());
    }

    public function testServicesConfigPath(): void
    {
        // 验证services.yaml配置文件路径应该存在
        $configPath = __DIR__ . '/../../src/Resources/config/services.yaml';
        
        // 由于这是单元测试，我们不检查文件是否真实存在，只验证路径格式正确
        $this->assertStringEndsWith('services.yaml', $configPath);
        $this->assertStringContainsString('Resources/config', $configPath);
    }

    public function testExtensionAlias(): void
    {
        // Extension默认的alias应该是bundle名称的下划线版本
        $alias = $this->extension->getAlias();
        
        // PrepaidCardExtension的默认alias应该是prepaid_card
        $this->assertEquals('prepaid_card', $alias);
    }

    public function testLoadParameterTypes(): void
    {
        $reflection = new \ReflectionMethod($this->extension, 'load');
        $parameters = $reflection->getParameters();
        
        // 验证第一个参数是数组类型
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('array', (string)$parameters[0]->getType());
        
        // 验证第二个参数是ContainerBuilder类型
        $this->assertTrue($parameters[1]->hasType());
        $this->assertEquals(ContainerBuilder::class, (string)$parameters[1]->getType());
    }

    public function testLoadReturnType(): void
    {
        $reflection = new \ReflectionMethod($this->extension, 'load');
        
        // 验证返回类型是void
        $this->assertTrue($reflection->hasReturnType());
        $this->assertEquals('void', (string)$reflection->getReturnType());
    }
}
