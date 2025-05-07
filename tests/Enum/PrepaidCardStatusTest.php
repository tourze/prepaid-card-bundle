<?php

namespace PrepaidCardBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Enum\PrepaidCardStatus;

class PrepaidCardStatusTest extends TestCase
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
}
