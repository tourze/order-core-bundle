<?php

namespace OrderCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;

/**
 * 合同价格服务 - 重构以降低复杂度
 * 每个方法职责单一，复杂度 < 10
 */
readonly class ContractPriceService
{
    public function __construct(
        private PriceFormatter $formatter,
    ) {
    }

    public function getDisplayPrice(Contract $contract): string
    {
        return $this->getDisplayPriceFromCollection($contract->getPrices());
    }

    public function getDisplayTaxPrice(Contract $contract): string
    {
        return $this->getDisplayTaxPriceFromCollection($contract->getPrices());
    }

    public function getPurePrice(Contract $contract): string
    {
        $currencyPrices = $this->collectNonFreightPrices($contract);
        $result = $this->formatter->formatCurrencyPrices($currencyPrices);

        return $this->formatter->formatOrDefault($result);
    }

    /** @return array<string, float> */
    private function collectNonFreightPrices(Contract $contract): array
    {
        return $this->collectFilteredPrices($contract->getPrices(), false, true);
    }

    /** @return array<string, float> */
    private function extractMoneyOnly(PriceAggregator $aggregator): array
    {
        $result = [];
        foreach ($aggregator->getCurrencyTotals() as $currency => $totals) {
            $result[$currency] = $totals['money'];
        }

        return $result;
    }

    /**
     * 通用价格筛选和聚合方法
     * @param Collection<int, OrderPrice>|array<int, OrderPrice> $prices
     * @return array<string, float>
     */
    private function collectFilteredPrices($prices, bool $isFreight, bool $requiresProduct): array
    {
        $aggregator = new PriceAggregator();
        $this->aggregateMatchingPrices($prices, $aggregator, $isFreight, $requiresProduct);

        return $this->extractMoneyOnly($aggregator);
    }

    /**
     * @param Collection<int, OrderPrice>|array<int, OrderPrice> $prices
     */
    private function aggregateMatchingPrices($prices, PriceAggregator $aggregator, bool $isFreight, bool $requiresProduct): void
    {
        foreach ($prices as $price) {
            if ($this->priceMatchesFilter($price, $isFreight, $requiresProduct)) {
                $aggregator->addPrice(
                    $price->getCurrency(),
                    (float) ($price->getMoney() ?? 0)
                );
            }
        }
    }

    private function priceMatchesFilter(OrderPrice $price, bool $isFreight, bool $requiresProduct): bool
    {
        $matchesFreight = PriceFilter::isFreightPrice($price) === $isFreight;
        $matchesProduct = !$requiresProduct || PriceFilter::hasProduct($price);

        return $matchesFreight && $matchesProduct;
    }

    public function getPureFreightPrice(Contract $contract): string
    {
        $currencyPrices = $this->collectFreightPrices($contract);
        $result = $this->formatter->formatCurrencyPrices($currencyPrices);

        return '' === $result ? '包邮' : $result;
    }

    /** @return array<string, float> */
    private function collectFreightPrices(Contract $contract): array
    {
        return $this->collectFilteredPrices($this->getAppendPrices($contract), true, false);
    }

    /** @return array<int, OrderPrice> */
    private function getAppendPrices(Contract $contract): array
    {
        return $this->filterPricesWithoutProduct($contract->getPrices());
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     * @return array<int, OrderPrice>
     */
    private function filterPricesWithoutProduct(Collection $prices): array
    {
        $result = [];
        foreach ($prices as $price) {
            if (!PriceFilter::hasProduct($price)) {
                $result[] = $price;
            }
        }

        return $result;
    }

    /** @return array<string, array{money: float, tax: float}> */
    public function getCurrencyPrices(Contract $contract): array
    {
        $aggregator = new PriceAggregator();
        $this->aggregateContractPrices($contract, $aggregator);
        $this->aggregateProductPrices($contract, $aggregator);

        return $aggregator->getCurrencyTotals();
    }

    private function aggregateContractPrices(Contract $contract, PriceAggregator $aggregator): void
    {
        $this->aggregatePricesFromCollection($contract->getPrices(), $aggregator);
    }

    private function aggregateProductPrices(Contract $contract, PriceAggregator $aggregator): void
    {
        foreach ($contract->getProducts() as $product) {
            $this->aggregatePricesFromCollection($product->getPrices(), $aggregator);
        }
    }

    /** @param Collection<int, OrderPrice> $prices */
    private function aggregatePricesFromCollection(Collection $prices, PriceAggregator $aggregator): void
    {
        foreach ($prices as $price) {
            $this->addPriceToAggregator($price, $aggregator);
        }
    }

    private function addPriceToAggregator(OrderPrice $price, PriceAggregator $aggregator): void
    {
        $aggregator->addPrice(
            $price->getCurrency(),
            (float) ($price->getMoney() ?? 0),
            (float) ($price->getTax() ?? 0)
        );
    }

    /** @return array<string, float> */
    public function getFreightPrices(Contract $contract): array
    {
        $aggregator = new PriceAggregator();
        foreach ($contract->getProducts() as $product) {
            $this->aggregateFreightPricesFromProduct($product, $aggregator);
        }

        return $this->extractMoneyOnly($aggregator);
    }

    private function aggregateFreightPricesFromProduct(OrderProduct $product, PriceAggregator $aggregator): void
    {
        $freightPrices = $this->collectFilteredPrices($product->getPrices(), true, false);
        foreach ($freightPrices as $currency => $money) {
            $aggregator->addPrice($currency, $money);
        }
    }

    public function calcPriceByCurrency(Contract $contract, string $currency): float
    {
        $aggregator = new PriceAggregator();
        $this->aggregateUnpaidPricesForCurrency($contract->getPrices(), $aggregator, $currency);

        return $aggregator->getTotalByCurrency($currency);
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     */
    private function aggregateUnpaidPricesForCurrency(Collection $prices, PriceAggregator $aggregator, string $currency): void
    {
        foreach ($prices as $price) {
            if ($this->isUnpaidPriceForCurrency($price, $currency)) {
                $aggregator->addPrice(
                    $currency,
                    (float) ($price->getMoney() ?? 0),
                    (float) ($price->getTax() ?? 0)
                );
            }
        }
    }

    private function isUnpaidPriceForCurrency(OrderPrice $price, string $currency): bool
    {
        return !PriceFilter::isPaid($price) && $price->getCurrency() === $currency;
    }

    public function isPayable(Contract $contract): bool
    {
        $prices = $this->getPayPrices($contract);

        return $this->hasCnyPayment($prices);
    }

    /** @param array<string, float> $prices */
    private function hasCnyPayment(array $prices): bool
    {
        return isset($prices['CNY']) && $prices['CNY'] > 0;
    }

    /** @return array<string, float> */
    public function getPayPrices(Contract $contract): array
    {
        return $this->aggregatePayPricesByCurrency($contract->getPrices());
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     * @return array<string, float>
     */
    private function aggregatePayPricesByCurrency(Collection $prices): array
    {
        $result = [];
        foreach ($prices as $price) {
            $currency = $price->getCurrency();
            $result[$currency] = ($result[$currency] ?? 0) + (float) ($price->getMoney() ?? 0);
        }

        return $result;
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     */
    public function getDisplayPriceFromCollection(Collection $prices): string
    {
        $aggregator = $this->aggregateCollectionPrices($prices, false);
        $result = $this->formatAggregatorResults($aggregator, false);

        return $this->formatter->formatOrDefault($result);
    }

    /** @param Collection<int, OrderPrice> $prices */
    private function aggregateCollectionPrices(Collection $prices, bool $includeTax = true): PriceAggregator
    {
        $aggregator = new PriceAggregator();
        foreach ($prices as $price) {
            $aggregator->addPrice(
                $price->getCurrency(),
                (float) ($price->getMoney() ?? 0),
                $includeTax ? (float) ($price->getTax() ?? 0) : 0
            );
        }

        return $aggregator;
    }

    private function formatAggregatorResults(PriceAggregator $aggregator, bool $includeTax = true): string
    {
        $results = $this->buildCurrencyAmounts($aggregator, $includeTax);

        return $this->formatter->formatCurrencyPrices($results);
    }

    /** @return array<string, float> */
    private function buildCurrencyAmounts(PriceAggregator $aggregator, bool $includeTax): array
    {
        $results = [];
        foreach ($aggregator->getCurrencyTotals() as $currency => $totals) {
            $amount = $this->calculateAmount($totals, $includeTax);
            if ($amount > 0) {
                $results[$currency] = $amount;
            }
        }

        return $results;
    }

    /**
     * @param array{money: float, tax: float} $totals
     */
    private function calculateAmount(array $totals, bool $includeTax): float
    {
        return $includeTax ? $totals['money'] + $totals['tax'] : $totals['money'];
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     */
    public function getDisplayTaxPriceFromCollection(Collection $prices): string
    {
        $aggregator = $this->aggregateCollectionPrices($prices, true);
        $result = $this->formatAggregatorResults($aggregator, true);

        return $this->formatter->formatOrDefault($result);
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getDisplayUnitPrice(Collection $prices, int $quantity): string
    {
        $aggregator = $this->aggregateCollectionPrices($prices, false);
        $result = $this->formatUnitPrices($aggregator, $quantity, false);

        return $this->formatter->formatOrDefault($result);
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getDisplayUnitTaxPrice(Collection $prices, int $quantity): string
    {
        $aggregator = $this->aggregateCollectionPrices($prices, true);
        $result = $this->formatUnitPrices($aggregator, $quantity, true);

        return $this->formatter->formatOrDefault($result);
    }

    private function formatUnitPrices(PriceAggregator $aggregator, int $quantity, bool $includeTax): string
    {
        if ($quantity <= 0) {
            return '';
        }

        $parts = $this->buildUnitPriceParts($aggregator, $quantity, $includeTax);

        return implode('+', $parts);
    }

    /**
     * @return array<int, string>
     */
    private function buildUnitPriceParts(PriceAggregator $aggregator, int $quantity, bool $includeTax): array
    {
        $parts = [];
        foreach ($aggregator->getCurrencyTotals() as $currency => $totals) {
            $unitFormatted = $this->formatUnitPriceForCurrency($totals, $currency, $quantity, $includeTax);
            if ('' !== $unitFormatted) {
                $parts[] = $unitFormatted;
            }
        }

        return $parts;
    }

    /**
     * @param array{money: float, tax: float} $totals
     */
    private function formatUnitPriceForCurrency(array $totals, string $currency, int $quantity, bool $includeTax): string
    {
        $amount = $this->calculateAmount($totals, $includeTax);

        return $this->formatter->formatUnitPrice($amount, $currency, $quantity);
    }

    /**
     * @param Collection<int, OrderPrice> $contractPrices
     * @return array<string, float>
     */
    public function getSaleUnitPrice(OrderProduct $orderProduct, Collection $contractPrices): array
    {
        $result = $this->getUnitPricesFromProduct($orderProduct);

        if ([] === $result) {
            $result = $this->getUnitPricesFromContract($orderProduct, $contractPrices);
        }

        return $result;
    }

    /** @return array<string, float> */
    private function getUnitPricesFromProduct(OrderProduct $orderProduct): array
    {
        $aggregator = new PriceAggregator();
        foreach ($orderProduct->getPrices() as $price) {
            $aggregator->addPrice(
                $price->getCurrency(),
                (float) ($price->getMoney() ?? 0)
            );
        }

        return $this->convertToUnitPrices($aggregator, $orderProduct->getQuantity());
    }

    /**
     * @param Collection<int, OrderPrice> $contractPrices
     * @return array<string, float>
     */
    private function getUnitPricesFromContract(OrderProduct $orderProduct, Collection $contractPrices): array
    {
        $aggregator = new PriceAggregator();
        foreach ($contractPrices as $price) {
            if ($price->getProduct() === $orderProduct) {
                $aggregator->addPrice(
                    $price->getCurrency(),
                    (float) ($price->getMoney() ?? 0)
                );
            }
        }

        return $this->convertToUnitPrices($aggregator, $orderProduct->getQuantity());
    }

    /** @return array<string, float> */
    private function convertToUnitPrices(PriceAggregator $aggregator, int $quantity): array
    {
        return $this->calculateUnitPricesFromAggregator($aggregator, $quantity);
    }

    /** @return array<string, float> */
    private function calculateUnitPricesFromAggregator(PriceAggregator $aggregator, int $quantity): array
    {
        $result = [];
        foreach ($aggregator->getCurrencyTotals() as $currency => $totals) {
            $result[$currency] = $this->calculateSafeUnitPrice($totals['money'], $quantity);
        }

        return $result;
    }

    private function calculateSafeUnitPrice(float $totalMoney, int $quantity): float
    {
        return $quantity > 0 ? $totalMoney / $quantity : 0;
    }

    /** @param Collection<int, OrderPrice> $prices
     * @return array<int, string>
     */
    public function collectNonFreightPricesFromCollection(Collection $prices): array
    {
        return $this->collectFormattedPrices($prices, false);
    }

    /** @param Collection<int, OrderPrice> $prices
     * @return array<int, string>
     */
    public function collectFreightPricesOnly(Collection $prices): array
    {
        return $this->collectFormattedPrices($prices, true);
    }

    /** @param Collection<int, OrderPrice> $prices
     * @return array<int, string>
     */
    private function collectFormattedPrices(Collection $prices, bool $onlyFreight): array
    {
        return $this->filterAndFormatPrices($prices, $onlyFreight);
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     * @return array<int, string>
     */
    private function filterAndFormatPrices(Collection $prices, bool $onlyFreight): array
    {
        $result = [];
        foreach ($prices as $price) {
            if ($this->matchesFreightFilter($price, $onlyFreight)) {
                $result[] = $this->formatPriceWithTax($price);
            }
        }

        return $result;
    }

    private function matchesFreightFilter(OrderPrice $price, bool $onlyFreight): bool
    {
        return $onlyFreight === PriceFilter::isFreightPrice($price);
    }

    private function formatPriceWithTax(OrderPrice $price): string
    {
        $moneyStr = $this->normalizeToNumericString($price->getMoney());
        $taxStr = $this->normalizeToNumericString($price->getTax());
        $total = bcadd($moneyStr, $taxStr, 2);

        return $price->getCurrency() . ' ' . $total;
    }

    /** @return numeric-string */
    private function normalizeToNumericString(mixed $value): string
    {
        return $this->convertValueToNumericString($value);
    }

    /** @return numeric-string */
    private function convertValueToNumericString(mixed $value): string
    {
        if (null === $value) {
            return '0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return '0';
    }

    // 恢复删除的方法以通过测试
    /**
     * @param Collection<int, OrderPrice> $prices
     * @return list<string>
     */
    public function formatPricesForDisplay(Collection $prices): array
    {
        return $this->buildDisplayPricesArray($prices);
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     * @return list<string>
     */
    private function buildDisplayPricesArray(Collection $prices): array
    {
        $result = [];
        foreach ($prices as $price) {
            $formatted = $this->formatPositivePriceForDisplay($price);
            if ('' !== $formatted) {
                $result[] = $formatted;
            }
        }

        return $result;
    }

    private function formatPositivePriceForDisplay(OrderPrice $price): string
    {
        $money = (float) ($price->getMoney() ?? 0);
        if ($money <= 0) {
            return '';
        }

        return number_format($money, 2, '.', '') . $price->getCurrency();
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function sumPriceByCurrency(Collection $prices, string $currency): float
    {
        $aggregator = $this->aggregateCollectionPrices($prices, false);

        return $aggregator->getMoneyByCurrency($currency);
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function sumTaxPriceByCurrency(Collection $prices, string $currency): float
    {
        $aggregator = $this->aggregateCollectionPrices($prices, true);

        return $aggregator->getTotalByCurrency($currency);
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getTotalTaxPrice(Collection $prices): float
    {
        $aggregator = $this->aggregateCollectionPrices($prices, true);

        return $this->sumTotalAmountsFromAggregator($aggregator);
    }

    private function sumTotalAmountsFromAggregator(PriceAggregator $aggregator): float
    {
        $total = 0;
        foreach ($aggregator->getCurrencyTotals() as $totals) {
            $total += $totals['money'] + $totals['tax'];
        }

        return $total;
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getTotalPrice(Collection $prices): float
    {
        $aggregator = $this->aggregateCollectionPrices($prices, false);

        return $this->sumMoneyFromAggregator($aggregator);
    }

    private function sumMoneyFromAggregator(PriceAggregator $aggregator): float
    {
        $total = 0;
        foreach ($aggregator->getCurrencyTotals() as $totals) {
            $total += $totals['money'];
        }

        return $total;
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getTotalTax(Collection $prices): float
    {
        $aggregator = $this->aggregateCollectionPrices($prices, true);

        return $this->sumTaxFromAggregator($aggregator);
    }

    private function sumTaxFromAggregator(PriceAggregator $aggregator): float
    {
        $total = 0;
        foreach ($aggregator->getCurrencyTotals() as $totals) {
            $total += $totals['tax'];
        }

        return $total;
    }

    /** @param Collection<int, OrderPrice> $prices */
    public function getTaxRate(Collection $prices): float
    {
        return $this->calculateTaxRatePercentage($prices);
    }

    /**
     * @param Collection<int, OrderPrice> $prices
     */
    private function calculateTaxRatePercentage(Collection $prices): float
    {
        $totalMoney = $this->getTotalPrice($prices);
        $totalTax = $this->getTotalTax($prices);

        return $this->computeTaxRateFromTotals($totalMoney, $totalTax);
    }

    private function computeTaxRateFromTotals(float $totalMoney, float $totalTax): float
    {
        if ($totalMoney <= 0) {
            return 0;
        }

        return round(($totalTax / $totalMoney) * 100, 2);
    }
}
