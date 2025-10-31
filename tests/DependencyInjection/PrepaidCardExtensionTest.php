<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\DependencyInjection\PrepaidCardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardExtension::class)]
final class PrepaidCardExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private PrepaidCardExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new PrepaidCardExtension();
    }

    public function testExtensionExtendsSymfonyExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function testExtensionIsInstantiable(): void
    {
        $this->assertInstanceOf(PrepaidCardExtension::class, $this->extension);
    }

    public function testLoadDoesNotThrowException(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $this->expectNotToPerformAssertions();
        $this->extension->load([], $container);
    }

    public function testGetAliasReturnsCorrectAlias(): void
    {
        $this->assertSame('prepaid_card', $this->extension->getAlias());
    }
}
