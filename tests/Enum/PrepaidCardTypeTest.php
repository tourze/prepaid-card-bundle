<?php

namespace PrepaidCardBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Enum\PrepaidCardType;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardType::class)]
final class PrepaidCardTypeTest extends AbstractEnumTestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('一次性全额付款', PrepaidCardType::ONE_TIME->getLabel());
        $this->assertEquals('定金后期结算', PrepaidCardType::AFTER->getLabel());
    }

    public function testEnumCases(): void
    {
        $cases = PrepaidCardType::cases();
        $this->assertCount(2, $cases);

        $this->assertContains(PrepaidCardType::ONE_TIME, $cases);
        $this->assertContains(PrepaidCardType::AFTER, $cases);
    }

    public function testEnumValues(): void
    {
        $this->assertEquals('one-time', PrepaidCardType::ONE_TIME->value);
        $this->assertEquals('after', PrepaidCardType::AFTER->value);
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (PrepaidCardType::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label);
        }
    }

    public function testToArray(): void
    {
        // 测试ONE_TIME
        $array = PrepaidCardType::ONE_TIME->toArray();
        $this->assertEquals('one-time', $array['value']);
        $this->assertEquals('一次性全额付款', $array['label']);

        // 测试AFTER
        $array = PrepaidCardType::AFTER->toArray();
        $this->assertEquals('after', $array['value']);
        $this->assertEquals('定金后期结算', $array['label']);
    }

    public function testUniqueValues(): void
    {
        $values = [];
        foreach (PrepaidCardType::cases() as $case) {
            $values[] = $case->value;
        }

        // 确保所有值都是唯一的
        $this->assertCount(count(array_unique($values)), $values);
    }

    public function testSerializability(): void
    {
        // 测试枚举值的序列化和反序列化
        $original = PrepaidCardType::ONE_TIME;
        $serialized = serialize($original);
        $unserialized = unserialize($serialized);

        $this->assertEquals($original, $unserialized);
        self::assertInstanceOf(PrepaidCardType::class, $unserialized);
        $this->assertEquals($original->value, $unserialized->value);
        $this->assertEquals($original->getLabel(), $unserialized->getLabel());
    }

    public function testFromValue(): void
    {
        // 测试从值创建枚举实例
        $cases = PrepaidCardType::cases();

        foreach ($cases as $case) {
            $fromValue = PrepaidCardType::from($case->value);
            $this->assertEquals($case, $fromValue);
            $this->assertEquals($case->value, $fromValue->value);
        }
    }

    public function testTryFromValue(): void
    {
        // 测试安全的从值创建枚举实例
        $this->assertEquals(PrepaidCardType::ONE_TIME, PrepaidCardType::tryFrom('one-time'));
        $this->assertEquals(PrepaidCardType::AFTER, PrepaidCardType::tryFrom('after'));

        // 测试无效值
        $invalidResult1 = PrepaidCardType::tryFrom('invalid');
        $invalidResult2 = PrepaidCardType::tryFrom('');
        $this->assertNull($invalidResult1);
        $this->assertNull($invalidResult2);
    }

    public function testInterfaceImplementation(): void
    {
        // 测试实现的接口
        $type = PrepaidCardType::ONE_TIME;

        $this->assertInstanceOf(Labelable::class, $type);
        $this->assertInstanceOf(Itemable::class, $type);
        $this->assertInstanceOf(Selectable::class, $type);
        $this->assertInstanceOf(BadgeInterface::class, $type);
    }

    public function testBusinessLogic(): void
    {
        // 测试业务逻辑相关的方法
        $oneTime = PrepaidCardType::ONE_TIME;
        $after = PrepaidCardType::AFTER;

        // 验证枚举值的不同
        $this->assertNotEquals($oneTime->value, $after->value);

        // 验证枚举值与字符串的对应关系
        $this->assertEquals('one-time', $oneTime->value);
        $this->assertEquals('after', $after->value);
    }

    public function testStringComparison(): void
    {
        // 测试字符串比较
        $this->assertEquals('one-time', PrepaidCardType::ONE_TIME->value);
        $this->assertEquals('after', PrepaidCardType::AFTER->value);

        // 测试枚举值是小写
        $this->assertStringContainsString('-', PrepaidCardType::ONE_TIME->value);
        $this->assertNotEquals('ONE-TIME', PrepaidCardType::ONE_TIME->value);
        $this->assertNotEquals('AFTER', PrepaidCardType::AFTER->value);
    }

    public function testGetBadge(): void
    {
        $this->assertEquals('primary', PrepaidCardType::ONE_TIME->getBadge());
        $this->assertEquals('info', PrepaidCardType::AFTER->getBadge());
    }
}
