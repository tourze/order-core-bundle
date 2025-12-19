<?php

declare(strict_types=1);

namespace OrderCoreBundle\Event;

use OrderCoreBundle\Entity\OrderProduct;
use Symfony\Contracts\EventDispatcher\Event;

final class AfterOrderProductRefundEvent extends Event
{
    use ContractAware;

    private OrderProduct $product;

    public function getProduct(): OrderProduct
    {
        return $this->product;
    }

    public function setProduct(OrderProduct $product): void
    {
        $this->product = $product;
    }
}
