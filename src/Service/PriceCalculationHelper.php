<?php

declare(strict_types=1);

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\OrderPrice;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 价格计算辅助类
 *
 * 提供基础的价格计算和格式化功能：
 * - 计算含税总价
 * - 数值标准化
 * - 价格格式化展示
 */
#[Autoconfigure(public: true)]
final class PriceCalculationHelper
{
    /**
     * 计算订单价格总额（金额 + 税费）
     */
    public function calculateTotal(OrderPrice $orderPrice): float
    {
        $money = $this->normalizeToFloat($orderPrice->getMoney());
        $tax = $this->normalizeToFloat($orderPrice->getTax());

        return $money + $tax;
    }

    /**
     * 将任意值标准化为数值字符串
     *
     * @param mixed $value 待标准化的值
     * @return numeric-string 标准化后的数值字符串，无法转换的返回 '0'
     */
    public function normalizeToNumericString(mixed $value): string
    {
        if (null === $value) {
            return '0';
        }

        // 处理数值类型（int, float）
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        // 处理字符串类型
        if (is_string($value)) {
            // 空字符串返回0
            if ('' === $value) {
                return '0';
            }

            // 检查是否为数字字符串
            if (is_numeric($value)) {
                return $value;
            }

            // 非数字字符串返回0
            return '0';
        }

        // 其他类型（bool, array, object等）一律返回0
        return '0';
    }

    /**
     * 格式化价格显示（含税总价）
     *
     * 格式：{币种} {总价，保留2位小数}
     */
    public function formatPriceWithTax(OrderPrice $orderPrice): string
    {
        $moneyStr = $this->normalizeToNumericString($orderPrice->getMoney());
        $taxStr = $this->normalizeToNumericString($orderPrice->getTax());

        // 使用 bcadd 进行高精度加法运算，保持2位小数
        $total = bcadd($moneyStr, $taxStr, 2);

        return sprintf('%s %s', $orderPrice->getCurrency(), $total);
    }

    /**
     * 将字符串或null转换为float
     */
    private function normalizeToFloat(?string $value): float
    {
        if (null === $value || '' === $value) {
            return 0.0;
        }

        if (!is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }
}
