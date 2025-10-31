<?php

namespace OrderCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum OrderType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case DEFAULT = 'default';
    case STORE_SERVICE = 'store-service';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => '实物销售',
            self::STORE_SERVICE => '门店服务',
        };
    }
}
