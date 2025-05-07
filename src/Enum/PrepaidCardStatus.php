<?php

namespace PrepaidCardBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum PrepaidCardStatus: string implements Labelable, Itemable, Selectable
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
}
