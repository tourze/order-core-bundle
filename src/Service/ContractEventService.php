<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * 订单生命周期中的事件处理
 */
#[AsDecorator(decorates: ContractService::class, priority: -999)]
class ContractEventService implements ContractService
{
    public function __construct(
        #[AutowireDecorated] private readonly ContractService $inner,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function createOrder(Contract $contract): void
    {
        $this->inner->createOrder($contract);
    }

    public function cancelOrder(Contract $contract, ?UserInterface $user = null, ?string $cancelReason = null): void
    {
        $this->inner->cancelOrder($contract, $user, $cancelReason);

        // 订单取消后的处理
        // Create a system user for automated operations
        $systemUser = $this->userManager->createUser(
            userIdentifier: 'system',
            password: '',
            roles: ['ROLE_SYSTEM']
        );

        $event = new AfterOrderCancelEvent();
        $event->setSender($user ?? $systemUser);
        $event->setReceiver($contract->getUser() ?? $systemUser);
        $event->setContract($contract);
        $this->eventDispatcher->dispatch($event);
    }

    public function payOrder(Contract $contract): void
    {
        $this->inner->payOrder($contract);
    }
}
