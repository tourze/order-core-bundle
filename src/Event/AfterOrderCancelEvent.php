<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

final class AfterOrderCancelEvent extends UserInteractionEvent
{
    use ContractAware;
}
