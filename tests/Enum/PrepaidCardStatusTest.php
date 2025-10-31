<?php

namespace PrepaidCardBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardStatus::class)]
final class PrepaidCardStatusTest extends AbstractEnumTestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('初始化', PrepaidCardStatus::INIT->getLabel());
        $this->assertEquals('生效中', PrepaidCardStatus::VALID->getLabel());
        $this->assertEquals('已过期', PrepaidCardStatus::EXPIRED->getLabel());
        $this->assertEquals('已使用', PrepaidCardStatus::EMPTY->getLabel());
    }

    public function testEnumCases(): void
    {
        $cases = PrepaidCardStatus::cases();
        $this->assertCount(4, $cases);

        $this->assertContains(PrepaidCardStatus::INIT, $cases);
        $this->assertContains(PrepaidCardStatus::VALID, $cases);
        $this->assertContains(PrepaidCardStatus::EXPIRED, $cases);
        $this->assertContains(PrepaidCardStatus::EMPTY, $cases);
    }

    public function testEnumValues(): void
    {
        $this->assertEquals('init', PrepaidCardStatus::INIT->value);
        $this->assertEquals('valid', PrepaidCardStatus::VALID->value);
        $this->assertEquals('expired', PrepaidCardStatus::EXPIRED->value);
        $this->assertEquals('empty', PrepaidCardStatus::EMPTY->value);
    }

    public function testToArray(): void
    {
        // 测试 INIT
        $array = PrepaidCardStatus::INIT->toArray();
        $this->assertEquals('init', $array['value']);
        $this->assertEquals('初始化', $array['label']);

        // 测试 VALID
        $array = PrepaidCardStatus::VALID->toArray();
        $this->assertEquals('valid', $array['value']);
        $this->assertEquals('生效中', $array['label']);

        // 测试 EXPIRED
        $array = PrepaidCardStatus::EXPIRED->toArray();
        $this->assertEquals('expired', $array['value']);
        $this->assertEquals('已过期', $array['label']);

        // 测试 EMPTY
        $array = PrepaidCardStatus::EMPTY->toArray();
        $this->assertEquals('empty', $array['value']);
        $this->assertEquals('已使用', $array['label']);
    }

    public function testInterfaceImplementation(): void
    {
        $status = PrepaidCardStatus::INIT;
        $this->assertInstanceOf(BadgeInterface::class, $status);
    }

    public function testGetBadge(): void
    {
        $this->assertEquals('warning', PrepaidCardStatus::INIT->getBadge());
        $this->assertEquals('success', PrepaidCardStatus::VALID->getBadge());
        $this->assertEquals('danger', PrepaidCardStatus::EXPIRED->getBadge());
        $this->assertEquals('secondary', PrepaidCardStatus::EMPTY->getBadge());
    }
}
