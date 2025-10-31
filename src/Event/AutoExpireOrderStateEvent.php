<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

class AutoExpireOrderStateEvent extends UserInteractionEvent
{
    use ContractAware;
}
