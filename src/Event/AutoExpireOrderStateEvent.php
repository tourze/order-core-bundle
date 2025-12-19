<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

final class AutoExpireOrderStateEvent extends UserInteractionEvent
{
    use ContractAware;
}
