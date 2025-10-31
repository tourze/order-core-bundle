<?php

namespace OrderCoreBundle\EventSubscriber;

use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\OrderReceivedEvent;
use OrderCoreBundle\Service\ContractLogService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * 记录订单流转的状态日志，方便我们去回溯订单
 */
class TrackLogSubscriber
{
    public function __construct(private readonly ContractLogService $contractLogService)
    {
    }

    #[AsEventListener(priority: 100)]
    public function afterOrderReceived(OrderReceivedEvent $event): void
    {
        $this->contractLogService->trackOrderState($event->getContract(), OrderState::RECEIVED);
    }
}
