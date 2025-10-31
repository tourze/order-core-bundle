<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\OrderPrice;
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * 价格筛选器 - 统一筛选逻辑
 * 消除重复的筛选条件
 */
class PriceFilter
{
    public static function isFreightPrice(OrderPrice $price): bool
    {
        // 检查类型是否为运费枚举或名称包含运费
        return '运费' === $price->getName() || PriceType::FREIGHT === $price->getType();
    }

    public static function hasProduct(OrderPrice $price): bool
    {
        return null !== $price->getProduct();
    }

    public static function isPaid(OrderPrice $price): bool
    {
        return true === $price->isPaid();
    }

    public static function isPositive(OrderPrice $price): bool
    {
        $money = (float) ($price->getMoney() ?? 0);
        $tax = (float) ($price->getTax() ?? 0);

        return ($money + $tax) > 0;
    }
}
