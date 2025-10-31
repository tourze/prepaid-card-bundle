<?php

namespace PrepaidCardBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum PrepaidCardExpireType: int implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case AFTER_ACTIVATION = 2;
    case SAME_WITH_CARD = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::SAME_WITH_CARD => '同卡有效期',
            self::AFTER_ACTIVATION => '激活后',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::SAME_WITH_CARD => self::SUCCESS,
            self::AFTER_ACTIVATION => self::WARNING,
        };
    }
}
