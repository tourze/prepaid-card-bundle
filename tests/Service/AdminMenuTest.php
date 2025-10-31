<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 测试设置逻辑
    }

    public function testServiceCreation(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $reflection = new \ReflectionClass($adminMenu);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeWithMenu(): void
    {
        $adminMenu = self::getService(AdminMenu::class);

        // 创建子项的匿名类实现
        $subItem = new class implements ItemInterface {
            private int $addChildCallCount = 0;

            private int $setUriCallCount = 0;

            private int $setAttributeCallCount = 0;

            public function addChild($child, array $options = []): ItemInterface
            {
                ++$this->addChildCallCount;

                return $this;
            }

            public function setUri(?string $uri): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                ++$this->setUriCallCount;

                return $this;
            }

            public function setAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                ++$this->setAttributeCallCount;
                // 验证icon属性以fas fa-开头
                if ('icon' === $name) {
                    if (!is_string($value) || !str_starts_with($value, 'fas fa-')) {
                        throw new \InvalidArgumentException('Icon must start with "fas fa-"');
                    }
                }

                return $this;
            }

            public function getAddChildCallCount(): int
            {
                return $this->addChildCallCount;
            }

            public function getSetUriCallCount(): int
            {
                return $this->setUriCallCount;
            }

            public function getSetAttributeCallCount(): int
            {
                return $this->setAttributeCallCount;
            }

            // 其他必需的接口方法
            public function getName(): string
            {
                return '';
            }

            public function setName(string $name): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabel(): string
            {
                return '';
            }

            public function setLabel(?string $label): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getUri(): ?string
            {
                return null;
            }

            public function getAttributes(): array
            {
                return [];
            }

            public function setAttributes(array $attributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getAttribute(string $name, $default = null)
            {
                return $default;
            }

            public function getExtra(string $name, $default = null)
            {
                return $default;
            }

            public function setExtra(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getExtras(): array
            {
                return [];
            }

            public function setExtras(array $extras): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getDisplayChildren(): bool
            {
                return true;
            }

            public function setDisplayChildren(bool $displayChildren): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isDisplayed(): bool
            {
                return true;
            }

            public function setDisplay(bool $display): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChild(string $name): ?ItemInterface
            {
                return null;
            }

            public function getChildren(): array
            {
                return [];
            }

            public function setChildren(array $children): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function removeChild($name): ItemInterface
            {
                return $this;
            }

            public function getFirstChild(): ItemInterface
            {
                return $this;
            }

            public function getLastChild(): ItemInterface
            {
                return $this;
            }

            public function hasChildren(): bool
            {
                return false;
            }

            public function setCurrent(?bool $current): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrent(): ?bool
            {
                return null;
            }

            public function isAncestor(): bool
            {
                return false;
            }

            public function getParent(): ?ItemInterface
            {
                return null;
            }

            public function setParent(?ItemInterface $parent = null): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getRoot(): ItemInterface
            {
                return $this;
            }

            public function isRoot(): bool
            {
                return true;
            }

            public function getLevel(): int
            {
                return 0;
            }

            public function setFactory(mixed $factory): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttributes(): array
            {
                return [];
            }

            public function setLinkAttributes(array $linkAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLinkAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttributes(): array
            {
                return [];
            }

            public function setChildrenAttributes(array $childrenAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setChildrenAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttributes(): array
            {
                return [];
            }

            public function setLabelAttributes(array $labelAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLabelAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrentAncestor(): bool
            {
                return false;
            }

            public function count(): int
            {
                return 0;
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator([]);
            }

            public function offsetExists($offset): bool
            {
                return false;
            }

            public function offsetGet($offset): ?ItemInterface
            {
                return null;
            }

            public function offsetSet($offset, $value): void
            {
            }

            public function offsetUnset($offset): void
            {
            }

            public function actsLikeFirst(): bool
            {
                return true;
            }

            public function actsLikeLast(): bool
            {
                return true;
            }

            public function copy(): ItemInterface
            {
                return clone $this;
            }

            public function isFirst(): bool
            {
                return true;
            }

            public function isLast(): bool
            {
                return true;
            }

            public function reorderChildren(array $order): ItemInterface
            {
                return $this;
            }
        };

        // 创建子菜单项的匿名类实现
        $childItem = new class($subItem) implements ItemInterface {
            private ItemInterface $subItem;

            private int $addChildCallCount = 0;

            public function __construct(ItemInterface $subItem)
            {
                $this->subItem = $subItem;
            }

            public function addChild($child, array $options = []): ItemInterface
            {
                ++$this->addChildCallCount;

                return $this->subItem;
            }

            public function getAddChildCallCount(): int
            {
                return $this->addChildCallCount;
            }

            // 其他必需的接口方法
            public function getName(): string
            {
                return '';
            }

            public function setName(string $name): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabel(): string
            {
                return '';
            }

            public function setLabel(?string $label): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getUri(): ?string
            {
                return null;
            }

            public function setUri(?string $uri): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getAttributes(): array
            {
                return [];
            }

            public function setAttributes(array $attributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getAttribute(string $name, $default = null)
            {
                return $default;
            }

            public function setAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getExtra(string $name, $default = null)
            {
                return $default;
            }

            public function setExtra(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getExtras(): array
            {
                return [];
            }

            public function setExtras(array $extras): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getDisplayChildren(): bool
            {
                return true;
            }

            public function setDisplayChildren(bool $displayChildren): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isDisplayed(): bool
            {
                return true;
            }

            public function setDisplay(bool $display): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChild(string $name): ?ItemInterface
            {
                return null;
            }

            public function getChildren(): array
            {
                return [];
            }

            public function setChildren(array $children): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function removeChild($name): ItemInterface
            {
                return $this;
            }

            public function getFirstChild(): ItemInterface
            {
                return $this;
            }

            public function getLastChild(): ItemInterface
            {
                return $this;
            }

            public function hasChildren(): bool
            {
                return false;
            }

            public function setCurrent(?bool $current): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrent(): ?bool
            {
                return null;
            }

            public function isAncestor(): bool
            {
                return false;
            }

            public function getParent(): ?ItemInterface
            {
                return null;
            }

            public function setParent(?ItemInterface $parent = null): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getRoot(): ItemInterface
            {
                return $this;
            }

            public function isRoot(): bool
            {
                return true;
            }

            public function getLevel(): int
            {
                return 0;
            }

            public function setFactory(mixed $factory): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttributes(): array
            {
                return [];
            }

            public function setLinkAttributes(array $linkAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLinkAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttributes(): array
            {
                return [];
            }

            public function setChildrenAttributes(array $childrenAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setChildrenAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttributes(): array
            {
                return [];
            }

            public function setLabelAttributes(array $labelAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLabelAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrentAncestor(): bool
            {
                return false;
            }

            public function count(): int
            {
                return 0;
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator([]);
            }

            public function offsetExists($offset): bool
            {
                return false;
            }

            public function offsetGet($offset): ?ItemInterface
            {
                return null;
            }

            public function offsetSet($offset, $value): void
            {
            }

            public function offsetUnset($offset): void
            {
            }

            public function actsLikeFirst(): bool
            {
                return true;
            }

            public function actsLikeLast(): bool
            {
                return true;
            }

            public function copy(): ItemInterface
            {
                return clone $this;
            }

            public function isFirst(): bool
            {
                return true;
            }

            public function isLast(): bool
            {
                return true;
            }

            public function reorderChildren(array $order): ItemInterface
            {
                return $this;
            }
        };

        // 创建主菜单项的匿名类实现
        $item = new class($childItem) implements ItemInterface {
            private ItemInterface $childItem;

            private int $getChildCallCount = 0;

            private int $addChildCallCount = 0;

            public function __construct(ItemInterface $childItem)
            {
                $this->childItem = $childItem;
            }

            public function getChild(string $name): ?ItemInterface
            {
                ++$this->getChildCallCount;

                // 第一次调用返回null，第二次返回childItem
                return 1 === $this->getChildCallCount ? null : $this->childItem;
            }

            public function addChild($child, array $options = []): ItemInterface
            {
                ++$this->addChildCallCount;

                return $this->childItem;
            }

            public function getGetChildCallCount(): int
            {
                return $this->getChildCallCount;
            }

            public function getAddChildCallCount(): int
            {
                return $this->addChildCallCount;
            }

            // 其他必需的接口方法
            public function getName(): string
            {
                return '';
            }

            public function setName(string $name): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabel(): string
            {
                return '';
            }

            public function setLabel(?string $label): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getUri(): ?string
            {
                return null;
            }

            public function setUri(?string $uri): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getAttributes(): array
            {
                return [];
            }

            public function setAttributes(array $attributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getAttribute(string $name, $default = null)
            {
                return $default;
            }

            public function setAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getExtra(string $name, $default = null)
            {
                return $default;
            }

            public function setExtra(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getExtras(): array
            {
                return [];
            }

            public function setExtras(array $extras): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getDisplayChildren(): bool
            {
                return true;
            }

            public function setDisplayChildren(bool $displayChildren): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isDisplayed(): bool
            {
                return true;
            }

            public function setDisplay(bool $display): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildren(): array
            {
                return [];
            }

            public function setChildren(array $children): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function removeChild($name): ItemInterface
            {
                return $this;
            }

            public function getFirstChild(): ItemInterface
            {
                return $this;
            }

            public function getLastChild(): ItemInterface
            {
                return $this;
            }

            public function hasChildren(): bool
            {
                return false;
            }

            public function setCurrent(?bool $current): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrent(): ?bool
            {
                return null;
            }

            public function isAncestor(): bool
            {
                return false;
            }

            public function getParent(): ?ItemInterface
            {
                return null;
            }

            public function setParent(?ItemInterface $parent = null): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getRoot(): ItemInterface
            {
                return $this;
            }

            public function isRoot(): bool
            {
                return true;
            }

            public function getLevel(): int
            {
                return 0;
            }

            public function setFactory(mixed $factory): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttributes(): array
            {
                return [];
            }

            public function setLinkAttributes(array $linkAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLinkAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLinkAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttributes(): array
            {
                return [];
            }

            public function setChildrenAttributes(array $childrenAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getChildrenAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setChildrenAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttributes(): array
            {
                return [];
            }

            public function setLabelAttributes(array $labelAttributes): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function getLabelAttribute(string $name, $default = null): mixed
            {
                return $default;
            }

            public function setLabelAttribute(string $name, $value): ItemInterface // @phpstan-ignore-line symplify.noReturnSetterMethod
            {
                return $this;
            }

            public function isCurrentAncestor(): bool
            {
                return false;
            }

            public function count(): int
            {
                return 0;
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator([]);
            }

            public function offsetExists($offset): bool
            {
                return false;
            }

            public function offsetGet($offset): ?ItemInterface
            {
                return null;
            }

            public function offsetSet($offset, $value): void
            {
            }

            public function offsetUnset($offset): void
            {
            }

            public function actsLikeFirst(): bool
            {
                return true;
            }

            public function actsLikeLast(): bool
            {
                return true;
            }

            public function copy(): ItemInterface
            {
                return clone $this;
            }

            public function isFirst(): bool
            {
                return true;
            }

            public function isLast(): bool
            {
                return true;
            }

            public function reorderChildren(array $order): ItemInterface
            {
                return $this;
            }
        };

        // 执行测试
        $adminMenu($item);

        // 验证调用次数
        $this->assertSame(2, $item->getGetChildCallCount(), 'getChild应该被调用2次');
        $this->assertSame(1, $item->getAddChildCallCount(), 'addChild应该被调用1次');
        $this->assertSame(6, $childItem->getAddChildCallCount(), 'childItem的addChild应该被调用6次');
        $this->assertSame(6, $subItem->getSetUriCallCount(), 'subItem的setUri应该被调用6次');
        $this->assertSame(6, $subItem->getSetAttributeCallCount(), 'subItem的setAttribute应该被调用6次');
    }
}
