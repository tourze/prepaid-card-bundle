<?php

namespace PrepaidCardBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum PrepaidCardStatus: string implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case INIT = 'init';
    case VALID = 'valid';
    case EXPIRED = 'expired';
    case EMPTY = 'empty';

    public function getLabel(): string
    {
        return match ($this) {
            self::INIT => '初始化',
            self::VALID => '生效中',
            self::EXPIRED => '已过期',
            self::EMPTY => '已使用',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::INIT => self::WARNING,
            self::VALID => self::SUCCESS,
            self::EXPIRED => self::DANGER,
            self::EMPTY => self::SECONDARY,
        };
    }
}
