<?php

namespace OrderCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 付款方式
 */
enum PayMethod: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case WEAPP = 'weapp';
    case COD = 'cod';
    case PROXY = 'proxy';
    case POINT = 'point';

    public function getLabel(): string
    {
        return match ($this) {
            self::WEAPP => '微信小程序',
            self::COD => '货到付款',
            self::PROXY => '代理支付',
            self::POINT => '积分支付',
        };
    }
}
