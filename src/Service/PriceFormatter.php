<?php

namespace OrderCoreBundle\Service;

/**
 * 价格格式化器 - 统一显示逻辑
 * 消除重复的格式化代码
 */
class PriceFormatter
{
    private string $freeLabel;

    public function __construct(?string $freeLabel = null)
    {
        $envLabel = $_ENV['DISPLAY_FREE_PRICE'] ?? '免费';
        $this->freeLabel = $freeLabel ?? (is_string($envLabel) ? $envLabel : '免费');
    }

    /** @param array<string, float> $currencyPrices */
    public function formatCurrencyPrices(array $currencyPrices): string
    {
        $result = [];
        foreach ($currencyPrices as $currency => $amount) {
            if ($amount > 0) {
                $result[] = number_format($amount, 2, '.', '') . $currency;
            }
        }

        return implode('+', $result);
    }

    public function formatOrDefault(string $formatted): string
    {
        if ('' === $formatted) {
            // 动态获取当前环境变量值，支持测试场景
            $envLabel = $_ENV['DISPLAY_FREE_PRICE'] ?? $this->freeLabel;
            $currentFreeLabel = is_string($envLabel) ? $envLabel : $this->freeLabel;

            return $currentFreeLabel;
        }

        return $formatted;
    }

    public function formatUnitPrice(float $amount, string $currency, int $quantity): string
    {
        if ($quantity <= 0 || $amount <= 0) {
            return '';
        }
        $unitPrice = $amount / $quantity;

        return number_format($unitPrice, 2, '.', '') . $currency;
    }
}
