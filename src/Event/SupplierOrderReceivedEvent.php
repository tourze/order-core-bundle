<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

/**
 * 订单完成了，通知供应商
 */
class SupplierOrderReceivedEvent extends UserInteractionEvent
{
    use ContractAware;
}
