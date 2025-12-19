<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

final class CreateOrderFailedEvent extends UserInteractionEvent
{
    use ContractAware;
}
