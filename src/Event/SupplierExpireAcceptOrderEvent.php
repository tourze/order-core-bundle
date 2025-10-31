<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

class SupplierExpireAcceptOrderEvent extends UserInteractionEvent
{
    use ContractAware;
}
