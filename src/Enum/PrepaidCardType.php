<?php

namespace PrepaidCardBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 预付卡类型
 */
enum PrepaidCardType: string implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case ONE_TIME = 'one-time';
    case AFTER = 'after';

    public function getLabel(): string
    {
        return match ($this) {
            self::ONE_TIME => '一次性全额付款',
            self::AFTER => '定金后期结算',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::ONE_TIME => self::PRIMARY,
            self::AFTER => self::INFO,
        };
    }
}
