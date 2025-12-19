<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

final class SupplierExpireAcceptOrderEvent extends UserInteractionEvent
{
    use ContractAware;
}
