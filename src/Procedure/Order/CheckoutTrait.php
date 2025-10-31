<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

/**
 * 结账通用功能 Trait
 * 遵循 KISS 原则：只包含最必要的共享逻辑
 */
trait CheckoutTrait
{
    /**
     * 验证商品列表是否有效
     *
     * @param array<mixed> $products
     */
    protected function validateProducts(array $products): bool
    {
        return [] !== $products;
    }

    /**
     * 生成订单编号
     * 简单实现：时间戳 + 随机数
     */
    protected function generateOrderSn(): string
    {
        return date('YmdHis') . mt_rand(1000, 9999);
    }
}
