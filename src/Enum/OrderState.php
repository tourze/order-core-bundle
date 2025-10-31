<?php

namespace OrderCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum OrderState: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case INIT = 'init';
    case CANCELED = 'canceled';
    case PAYING = 'paying';
    case PAID = 'paid';
    case PART_SHIPPED = 'part-shipped';
    case SHIPPED = 'shipped';
    case RECEIVED = 'received';
    case EXPIRED = 'expired';
    case AFTERSALES_ING = 'aftersales-ing';
    case AFTERSALES_SUCCESS = 'aftersales-success';
    case AFTERSALES_FAILED = 'aftersales-failed';
    case AUDITING = 'auditing';
    case ACCEPT_ORDER = 'accept';
    case REJECT_ORDER = 'reject';
    case EXCEPTION = 'exception';

    public function getLabel(): string
    {
        return match ($this) {
            self::INIT => '已创建',
            self::CANCELED => '已取消',
            self::PAYING => '支付中',
            self::PAID => '已支付',
            self::PART_SHIPPED => '部分发货',
            self::SHIPPED => '已发货',
            self::RECEIVED => '已完成',
            self::EXPIRED => '已过期',

            self::AFTERSALES_ING => '售后申请中',
            self::AFTERSALES_SUCCESS => '售后成功',
            self::AFTERSALES_FAILED => '售后失败',

            self::AUDITING => '审核中',
            self::ACCEPT_ORDER => '已接单',
            self::REJECT_ORDER => '已拒单',
            self::EXCEPTION => '订单失败',
        };
    }
}
