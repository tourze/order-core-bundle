<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

class AfterOrderCancelEvent extends UserInteractionEvent
{
    use ContractAware;
}
