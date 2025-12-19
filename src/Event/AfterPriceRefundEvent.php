<?php

namespace OrderCoreBundle\Event;

use OrderCoreBundle\Entity\OrderPrice;
use Symfony\Contracts\EventDispatcher\Event;

final class AfterPriceRefundEvent extends Event
{
    use ContractAware;

    private OrderPrice $price;

    public function getPrice(): OrderPrice
    {
        return $this->price;
    }

    public function setPrice(OrderPrice $price): void
    {
        $this->price = $price;
    }
}
