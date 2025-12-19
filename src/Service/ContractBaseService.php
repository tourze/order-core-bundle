<?php

namespace OrderCoreBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use OrderCoreBundle\Event\AfterOrderCreatedEvent;
use OrderCoreBundle\Event\BeforeOrderCreatedEvent;
use OrderCoreBundle\Event\CreateOrderFailedEvent;
use OrderCoreBundle\Event\OrderPaidEvent;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\UserServiceContracts\UserManagerInterface;

#[AsAlias(id: ContractService::class)]
#[WithMonologChannel(channel: 'order_core')]
final readonly class ContractBaseService implements ContractService
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager,
        private ContractPriceService $priceService,
        private UserManagerInterface $userManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function createOrder(Contract $contract): void
    {
        try {
            // 订单创建前
            $event = new BeforeOrderCreatedEvent();
            $event->setContract($contract);
            $this->eventDispatcher->dispatch($event);

            // 如果不需要支付人民币的话，不需要继续支付
            $payPrices = $this->priceService->getPayPrices($contract);
            if (isset($payPrices['CNY']) && $payPrices['CNY'] > 0) {
                $contract->setState(OrderState::INIT);
            } else {
                // 不用给钱，就当做已支付处理，分发事件出去
                $contract->setState(OrderState::PAID);
            }

            $this->entityManager->persist($contract);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('创建订单时发生未知异常', [
                'exception' => $exception,
            ]);

            $event = new CreateOrderFailedEvent();
            $event->setContract($contract);
            $this->eventDispatcher->dispatch($event);

            throw $exception;
        }

        // 订单创建后
        $event = new AfterOrderCreatedEvent();
        $event->setContract($contract);
        $this->eventDispatcher->dispatch($event);
    }

    public function cancelOrder(Contract $contract, ?UserInterface $user = null, ?string $cancelReason = null): void
    {
        $contract->setState(OrderState::CANCELED);
        $contract->setCancelTime(CarbonImmutable::now());
        if (null !== $cancelReason) {
            $contract->setCancelReason($cancelReason);
        }
        $this->entityManager->persist($contract);
        $this->entityManager->flush();

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

    /**
     * 支付成功通知
     */
    public function payOrder(Contract $contract): void
    {
        $event = new OrderPaidEvent();
        $event->setContract($contract);
        $this->eventDispatcher->dispatch($event);
    }
}
