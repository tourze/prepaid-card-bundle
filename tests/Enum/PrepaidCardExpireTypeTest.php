<?php

namespace PrepaidCardBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;

class PrepaidCardExpireTypeTest extends TestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('同卡有效期', PrepaidCardExpireType::SAME_WITH_CARD->getLabel());
        $this->assertEquals('激活后', PrepaidCardExpireType::AFTER_ACTIVATION->getLabel());
    }

    public function testEnumCases(): void
    {
        $cases = PrepaidCardExpireType::cases();
        $this->assertCount(2, $cases);

        $this->assertContains(PrepaidCardExpireType::SAME_WITH_CARD, $cases);
        $this->assertContains(PrepaidCardExpireType::AFTER_ACTIVATION, $cases);
    }

    public function testEnumValues(): void
    {
        $this->assertEquals(1, PrepaidCardExpireType::SAME_WITH_CARD->value);
        $this->assertEquals(2, PrepaidCardExpireType::AFTER_ACTIVATION->value);
    }

    public function testToSelectItem(): void
    {
        // 测试SAME_WITH_CARD
        $item = PrepaidCardExpireType::SAME_WITH_CARD->toSelectItem();
        $this->assertEquals('同卡有效期', $item['label']);
        $this->assertEquals('同卡有效期', $item['text']);
        $this->assertEquals(1, $item['value']);
        $this->assertEquals('同卡有效期', $item['name']);

        // 测试AFTER_ACTIVATION
        $item = PrepaidCardExpireType::AFTER_ACTIVATION->toSelectItem();
        $this->assertEquals('激活后', $item['label']);
        $this->assertEquals('激活后', $item['text']);
        $this->assertEquals(2, $item['value']);
        $this->assertEquals('激活后', $item['name']);
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (PrepaidCardExpireType::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label);
        }
    }

    public function testToArray(): void
    {
        // 测试SAME_WITH_CARD
        $array = PrepaidCardExpireType::SAME_WITH_CARD->toArray();
        $this->assertEquals(1, $array['value']);
        $this->assertEquals('同卡有效期', $array['label']);

        // 测试AFTER_ACTIVATION
        $array = PrepaidCardExpireType::AFTER_ACTIVATION->toArray();
        $this->assertEquals(2, $array['value']);
        $this->assertEquals('激活后', $array['label']);
    }

    public function testUniqueValues(): void
    {
        $values = [];
        foreach (PrepaidCardExpireType::cases() as $case) {
            $values[] = $case->value;
        }

        // 确保所有值都是唯一的
        $this->assertEquals(count($values), count(array_unique($values)));
    }

    public function testSerializability(): void
    {
        // 测试枚举值的序列化和反序列化
        $original = PrepaidCardExpireType::SAME_WITH_CARD;
        $serialized = serialize($original);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($original, $unserialized);
        $this->assertEquals($original->value, $unserialized->value);
        $this->assertEquals($original->getLabel(), $unserialized->getLabel());
    }

    public function testFromValue(): void
    {
        // 测试从值创建枚举实例
        $cases = PrepaidCardExpireType::cases();
        
        foreach ($cases as $case) {
            $fromValue = PrepaidCardExpireType::from($case->value);
            $this->assertEquals($case, $fromValue);
            $this->assertEquals($case->value, $fromValue->value);
        }
    }

    public function testTryFromValue(): void
    {
        // 测试安全的从值创建枚举实例
        $this->assertEquals(PrepaidCardExpireType::SAME_WITH_CARD, PrepaidCardExpireType::tryFrom(1));
        $this->assertEquals(PrepaidCardExpireType::AFTER_ACTIVATION, PrepaidCardExpireType::tryFrom(2));
        
        // 测试无效值
        $this->assertNull(PrepaidCardExpireType::tryFrom(999));
        $this->assertNull(PrepaidCardExpireType::tryFrom(0));
    }

    public function testInterfaceImplementation(): void
    {
        // 测试实现的接口
        $type = PrepaidCardExpireType::SAME_WITH_CARD;
        
        $this->assertInstanceOf(\Tourze\EnumExtra\Labelable::class, $type);
        $this->assertInstanceOf(\Tourze\EnumExtra\Itemable::class, $type);
        $this->assertInstanceOf(\Tourze\EnumExtra\Selectable::class, $type);
    }
} 