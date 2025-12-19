<?php

namespace OrderCoreBundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use OrderCoreBundle\Event\AfterOrderCreatedEvent;
use OrderCoreBundle\Event\OrderPaidEvent;
use OrderCoreBundle\Event\OrderReceivedEvent;
use OrderCoreBundle\Repository\OrderLogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * 记录订单流转的状态日志，方便我们去回溯订单
 */
#[WithMonologChannel(channel: 'order_core')]
final readonly class TrackLogSubscriber
{
    public function __construct(
        private OrderLogRepository $logRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 记录订单状态
     */
    public function trackOrderState(Contract $contract, ?OrderState $state = null): void
    {
        $log = new OrderLog();
        $log->setContract($contract);
        $log->setOrderSn($contract->getSn());
        $log->setCurrentState($state ?? $contract->getState());

        try {
            $this->logRepository->save($log);
        } catch (\Throwable $exception) {
            $this->logger->error('记录订单日志失败', [
                'exception' => $exception,
                'contract' => $contract,
            ]);
        }
    }

    #[AsEventListener(priority: -100)]
    public function afterOrderCreated(AfterOrderCreatedEvent $event): void
    {
        $this->trackOrderState($event->getContract(), OrderState::INIT);
    }

    #[AsEventListener(priority: -100)]
    public function afterOrderCancel(AfterOrderCancelEvent $event): void
    {
        $this->trackOrderState($event->getContract(), OrderState::CANCELED);
    }

    #[AsEventListener(priority: 100)]
    public function afterOrderPaid(OrderPaidEvent $event): void
    {
        $this->trackOrderState($event->getContract(), OrderState::PAID);
    }

    #[AsEventListener(priority: 100)]
    public function afterOrderReceived(OrderReceivedEvent $event): void
    {
        $this->trackOrderState($event->getContract(), OrderState::RECEIVED);
    }
}
