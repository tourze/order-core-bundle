<?php

namespace OrderCoreBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\OrderPaidEvent;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsAlias(id: ContractService::class)]
class ContractBaseService implements ContractService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContractPriceService $priceService,
    ) {
    }

    public function createOrder(Contract $contract): void
    {
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
