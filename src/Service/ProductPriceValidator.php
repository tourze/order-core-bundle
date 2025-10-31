<?php

declare(strict_types=1);

namespace OrderCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 产品价格验证器
 *
 * 负责验证订单价格的有效性，主要验证规则：
 * 1. 如果价格关联产品且产品在给定集合中，则检查产品是否有效
 * 2. 如果价格无关联产品或产品不在集合中，则检查总价是否非负
 */
#[Autoconfigure(public: true)]
final class ProductPriceValidator
{
    public function __construct(
        private readonly PriceCalculationHelper $priceCalculationHelper,
    ) {
    }

    /**
     * 验证订单价格在给定币种下是否有效
     *
     * 验证逻辑：
     * 1. 如果订单价格未关联产品，则价格总额非负即有效
     * 2. 如果关联产品且产品ID为null，则价格总额非负即有效
     * 3. 如果关联产品且产品在给定集合中存在，则检查产品是否有效
     * 4. 如果关联产品但产品不在给定集合中，则价格总额非负即有效
     *
     * @param OrderPrice $orderPrice 待验证的订单价格
     * @param Collection<int, OrderProduct> $products 产品集合，用于查找关联产品
     * @return bool 验证结果，true表示有效
     */
    public function isValidForCurrency(OrderPrice $orderPrice, Collection $products): bool
    {
        $associatedProduct = $orderPrice->getProduct();

        // 情况1：没有关联产品，检查总价是否非负
        if (null === $associatedProduct) {
            return $this->isTotalNonNegative($orderPrice);
        }

        // 情况2：关联产品但产品ID为null，检查总价是否非负
        if (null === $associatedProduct->getId()) {
            return $this->isTotalNonNegative($orderPrice);
        }

        // 情况3：查找关联产品在给定集合中的实例
        $productInCollection = $this->findProductInCollection($associatedProduct, $products);

        if (null !== $productInCollection) {
            // 产品在集合中，需要同时检查产品有效性和总价非负
            return true === $productInCollection->isValid() && $this->isTotalNonNegative($orderPrice);
        }

        // 情况4：关联产品但不在给定集合中，检查总价是否非负
        return $this->isTotalNonNegative($orderPrice);
    }

    /**
     * 检查价格总额是否非负
     */
    private function isTotalNonNegative(OrderPrice $orderPrice): bool
    {
        $total = $this->priceCalculationHelper->calculateTotal($orderPrice);

        return $total >= 0;
    }

    /**
     * 在产品集合中查找指定产品
     *
     * @param OrderProduct $targetProduct 目标产品
     * @param Collection<int, OrderProduct> $products 产品集合
     * @return OrderProduct|null 找到的产品实例，未找到返回null
     */
    private function findProductInCollection(OrderProduct $targetProduct, Collection $products): ?OrderProduct
    {
        $targetId = $targetProduct->getId();

        if (null === $targetId) {
            return null;
        }

        foreach ($products as $product) {
            if ($targetId === $product->getId()) {
                return $product;
            }
        }

        return null;
    }
}
