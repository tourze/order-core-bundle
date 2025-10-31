<?php

namespace OrderCoreBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsDecorator(decorates: ContractService::class, priority: 100)]
#[WithMonologChannel(channel: 'order_core')]
readonly class ContractLogService implements ContractService
{
    public function __construct(
        #[AutowireDecorated] private ContractService $inner,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function createOrder(Contract $contract): void
    {
        $this->inner->createOrder($contract);
        $this->trackOrderState($contract, OrderState::INIT);
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
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('记录订单日志失败', [
                'exception' => $exception,
                'contract' => $contract,
            ]);
        }
    }

    public function cancelOrder(Contract $contract, ?UserInterface $user = null, ?string $cancelReason = null): void
    {
        $this->inner->cancelOrder($contract, $user, $cancelReason);
        $this->trackOrderState($contract, OrderState::CANCELED);
    }

    public function payOrder(Contract $contract): void
    {
        $this->inner->payOrder($contract);
        $this->trackOrderState($contract, OrderState::PAID);
    }
}
