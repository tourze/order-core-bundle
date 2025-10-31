<?php

namespace OrderCoreBundle\Service;

/**
 * 价格聚合器 - 核心数据结构
 * 消除重复的聚合逻辑
 */
class PriceAggregator
{
    /** @var array<string, array{money: float, tax: float}> */
    private array $currencyTotals = [];

    public function addPrice(string $currency, float $money, float $tax = 0): void
    {
        if (!isset($this->currencyTotals[$currency])) {
            $this->currencyTotals[$currency] = ['money' => 0, 'tax' => 0];
        }
        $this->currencyTotals[$currency]['money'] += $money;
        $this->currencyTotals[$currency]['tax'] += $tax;
    }

    /** @return array<string, array{money: float, tax: float}> */
    public function getCurrencyTotals(): array
    {
        return $this->currencyTotals;
    }

    public function getTotalByCurrency(string $currency): float
    {
        return ($this->currencyTotals[$currency]['money'] ?? 0) + ($this->currencyTotals[$currency]['tax'] ?? 0);
    }

    public function getMoneyByCurrency(string $currency): float
    {
        return $this->currencyTotals[$currency]['money'] ?? 0;
    }

    public function isEmpty(): bool
    {
        return [] === $this->currencyTotals;
    }
}
