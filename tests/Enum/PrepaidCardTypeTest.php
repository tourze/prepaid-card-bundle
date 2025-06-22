<?php

namespace PrepaidCardBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Enum\PrepaidCardType;

class PrepaidCardTypeTest extends TestCase
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

    public function testToSelectItem(): void
    {
        // 测试ONE_TIME
        $item = PrepaidCardType::ONE_TIME->toSelectItem();
        $this->assertEquals('一次性全额付款', $item['label']);
        $this->assertEquals('一次性全额付款', $item['text']);
        $this->assertEquals('one-time', $item['value']);
        $this->assertEquals('一次性全额付款', $item['name']);

        // 测试AFTER
        $item = PrepaidCardType::AFTER->toSelectItem();
        $this->assertEquals('定金后期结算', $item['label']);
        $this->assertEquals('定金后期结算', $item['text']);
        $this->assertEquals('after', $item['value']);
        $this->assertEquals('定金后期结算', $item['name']);
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
        $this->assertEquals(count($values), count(array_unique($values)));
    }

    public function testSerializability(): void
    {
        // 测试枚举值的序列化和反序列化
        $original = PrepaidCardType::ONE_TIME;
        $serialized = serialize($original);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($original, $unserialized);
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
        $this->assertNull(PrepaidCardType::tryFrom('invalid'));
        $this->assertNull(PrepaidCardType::tryFrom(''));
    }

    public function testInterfaceImplementation(): void
    {
        // 测试实现的接口
        $type = PrepaidCardType::ONE_TIME;
        
        $this->assertInstanceOf(\Tourze\EnumExtra\Labelable::class, $type);
        $this->assertInstanceOf(\Tourze\EnumExtra\Itemable::class, $type);
        $this->assertInstanceOf(\Tourze\EnumExtra\Selectable::class, $type);
    }

    public function testBusinessLogic(): void
    {
        // 测试业务逻辑相关的方法
        $oneTime = PrepaidCardType::ONE_TIME;
        $after = PrepaidCardType::AFTER;

        // 验证枚举值的不同
        $this->assertNotSame($oneTime, $after);
        $this->assertNotEquals($oneTime->value, $after->value);
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
} 