<?php

namespace OrderCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * 订单支付成功时触发
 */
class OrderPaidEvent extends Event
{
    use ContractAware;
}
