<?php

namespace OrderCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ProductStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case RECEIVED = 'received';
    case HAD_RECEIVED = '已收到货';
    case NOT_RECEIVED = 'not_received';

    public function getLabel(): string
    {
        return match ($this) {
            self::RECEIVED => '已收到货',
            self::HAD_RECEIVED => '已收到货',
            self::NOT_RECEIVED => '未收到货',
        };
    }
}
