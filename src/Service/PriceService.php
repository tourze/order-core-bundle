<?php

namespace OrderCoreBundle\Service;

// TODO: Uncomment when AppBundle is available
// use AppBundle\Service\CurrencyManager;
use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\ProductCoreBundle\Entity\Sku;

#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'order_core')]
class PriceService
{
    public function __construct(
        // TODO: Uncomment when AppBundle is available
        // private readonly CurrencyManager $currencyManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 含税 - 传入多个订单，我们计算订单的总和
     *
     * @param array|Contract[] $orders
     */
    /**
     * @param array<int, Contract> $orders
     * @return array<int, array<string, mixed>>
     */
    public function getOrderTotalTaxPrices(array $orders): array
    {
        $prices = $this->calculateTotalPricesByCurrency($orders, true);

        return $this->formatPriceResults($prices);
    }

    /**
     * 不含税 - 传入多个订单，我们计算订单的总和
     *
     * @param array|Contract[] $orders
     */
    /**
     * @param array<int, Contract> $orders
     * @return array<int, array<string, mixed>>
     */
    public function getOrderTotalPrices(array $orders): array
    {
        $prices = $this->calculateTotalPricesByCurrency($orders, false);

        return $this->formatPriceResults($prices);
    }

    /**
     * @param array<int, Contract> $orders
     * @return array<string, string>
     */
    private function calculateTotalPricesByCurrency(array $orders, bool $includeTax): array
    {
        $prices = [];
        $orderCount = count($orders);
        $this->logger->debug('开始计算订单总价格', [
            'order_count' => $orderCount,
            'include_tax' => $includeTax,
        ]);

        foreach ($orders as $order) {
            foreach ($order->getPrices() as $price) {
                if (!$this->isPriceValid($price)) {
                    $this->logger->debug('跳过无效价格', [
                        'price_id' => $price->getId(),
                        'price_name' => $price->getName(),
                        'currency' => $price->getCurrency(),
                    ]);
                    continue;
                }

                $amount = $this->calculatePriceAmount($price, $includeTax);
                assert(is_numeric($amount));
                if ($includeTax && bccomp($amount, '0', 2) < 0) {
                    $this->logger->warning('跳过负数含税价格', [
                        'price_id' => $price->getId(),
                        'price_name' => $price->getName(),
                        'amount' => $amount,
                        'currency' => $price->getCurrency(),
                    ]);
                    continue;
                }

                $prices = $this->addToPriceByCurrency($prices, $price->getCurrency(), $amount);
            }
        }

        $this->logger->debug('完成订单总价格计算', [
            'currencies' => array_keys($prices),
            'total_currencies' => count($prices),
        ]);

        return $prices;
    }

    /**
     * @param Contract $order
     * @return array<string, string>
     */
    public function calculateTotalPricesByType(Contract $order, bool $includeTax): array
    {
        $prices = [
            'sale' => '0',
            'cost' => '0',
            'compete' => '0',
            'freight' => '0',
            'marketing' => '0',
            'original_price' => '0',
        ];

        foreach ($order->getPrices() as $price) {
            if (!$this->isPriceValid($price)) {
                continue;
            }
            $priceAmount = '0';
            if ($includeTax) {
                $money = $price->getMoney() ?? '0';
                $tax = $price->getTax() ?? '0';
                assert(is_numeric($money));
                assert(is_numeric($tax));
                $priceAmount = bcadd($money, $tax, 2);
            } else {
                $priceAmount = $price->getMoney() ?? '0';
            }
            assert(is_numeric($priceAmount));
            $priceTypeKey = $price->getType()->value;
            if (isset($prices[$priceTypeKey])) {
                $prices[$priceTypeKey] = bcadd($prices[$priceTypeKey], $priceAmount, 2);
            }
        }
        $saleAndFreight = bcadd($prices['sale'], $prices['freight'], 2);
        $total = bcsub($saleAndFreight, $prices['marketing'], 2);
        $prices['total'] = bccomp($total, '0', 2) >= 0 ? $total : '0';

        return $prices;
    }

    private function isPriceValid(OrderPrice $price): bool
    {
        // 如果这个价格跟商品有关，还需要看这个商品是否有效喔
        $product = $price->getProduct();

        return null === $product || true === $product->isValid();
    }

    private function calculatePriceAmount(OrderPrice $price, bool $includeTax = false): string
    {
        if ($includeTax) {
            $money = $price->getMoney() ?? '0';
            $tax = $price->getTax() ?? '0';
            assert(is_numeric($money));
            assert(is_numeric($tax));

            return bcadd($money, $tax, 2);
        }

        return $price->getMoney() ?? '0';
    }

    /**
     * @param array<string, string> $prices
     * @return array<string, string>
     */
    private function addToPriceByCurrency(array $prices, string $currency, string $amount): array
    {
        if (!isset($prices[$currency])) {
            $prices[$currency] = '0';
        }

        assert(is_numeric($prices[$currency]));
        assert(is_numeric($amount));
        $prices[$currency] = bcadd($prices[$currency], $amount, 2);

        return $prices;
    }

    /**
     * @param array<string, string> $prices
     * @return array<int, array<string, mixed>>
     */
    private function formatPriceResults(array $prices): array
    {
        $result = [];
        foreach ($prices as $currency => $price) {
            $result[] = $this->createPriceResult($currency, $price);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function createPriceResult(string $currency, string $amount): array
    {
        return [
            'currency' => [
                'code' => $currency,
                'symbol' => 'CNY' === $currency ? '￥' : '',
            ],
            'amount' => $amount,
            // TODO: Uncomment when CurrencyManager is available
            // 'display' => $this->currencyManager->getDisplayPrice($currency, $amount),
            'display' => $currency . ' ' . $amount,
        ];
    }

    /**
     * 根据运费ID和SKU列表查找运费价格
     *
     * @param array<string|int, Sku> $skus
     */
    public function findFreightPriceBySkus(string $freightId, array $skus): ?OrderPrice
    {
        $this->logger->info('查找运费价格', [
            'freight_id' => $freightId,
            'sku_count' => count($skus),
            'sku_ids' => array_keys($skus),
        ]);

        // 当前实现返回null，表示没有找到运费价格
        // 实际实现中，这里应该：
        // 1. 根据 freightId 查询运费模板
        // 2. 根据 SKU 列表计算运费
        // 3. 创建并返回 OrderPrice 对象

        $this->logger->debug('运费价格查找完成，未找到运费价格', [
            'freight_id' => $freightId,
        ]);

        return null;
    }
}
