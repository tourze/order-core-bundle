<?php

namespace OrderCoreBundle\EventSubscriber;

use OrderCoreBundle\Event\OrderPaidEvent;
use OrderCoreBundle\Service\ProductCoreServiceWrapper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\ProductCoreBundle\Service\SkuService;

class SkuStockEventSubscriber
{
    public function __construct(
        private readonly SkuService $skuService,
        private readonly ProductCoreServiceWrapper $productCoreServiceWrapper,
    ) {
    }

    #[AsEventListener]
    public function onOrderPaidEvent(OrderPaidEvent $event): void
    {
        $contract = $event->getContract();
        $products = $contract->getProducts();
        foreach ($products as $product) {
            // 保存sku销量
            $sku = $product->getSku();
            if (null !== $sku) {
                $this->productCoreServiceWrapper->safeIncreaseSalesReal($this->skuService, (string) $sku->getId(), $product->getQuantity() ?? 0);
            }
        }
    }
}
