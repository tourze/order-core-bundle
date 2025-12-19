<?php

namespace OrderCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * 订单被确认收货时触发
 */
final class OrderReceivedEvent extends Event
{
    use ContractAware;
}
