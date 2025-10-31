<?php

namespace OrderCoreBundle\Service;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
// OrderService中的发货相关功能已迁移到deliver-order-bundle，可通过DeliveryDataService获取
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\AfterPriceRefundEvent;
use OrderCoreBundle\Event\BeforePriceRefundEvent;
use OrderCoreBundle\Event\OrderReceivedEvent;
use OrderCoreBundle\Exception\ContractNotFoundException;
use OrderCoreBundle\Exception\FeatureMigratedException;
use OrderCoreBundle\Exception\OrderReceiveExpiredException;
use OrderCoreBundle\Exception\OrderReceiveNotStartedException;
use OrderCoreBundle\Repository\ContractRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Symfony\AopDoctrineBundle\Attribute\Transactional;

/**
 * 统一的订单服务，暂时未实现
 */
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'order_core')]
class OrderService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ContractService $contractService,
        private readonly DeliverOperationInterface $deliverOperation,
        private readonly ContractRepository $contractRepository,
    ) {
    }

    /**
     * 整单退款
     */
    public function refundOrder(Contract $order): void
    {
        foreach ($order->getProducts() as $product) {
            $this->refundProduct($product);
        }

        // 有一些费用不跟订单相关的，我们最后退
        foreach ($order->getPrices() as $price) {
            $this->refundPrice($price);
        }
    }

    /**
     * 订单支付信息退款
     * 将指定商品相关的支付信息都退掉了
     */
    public function refundProduct(OrderProduct $product): void
    {
        foreach ($product->getPrices() as $price) {
            $this->refundPrice($price);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * 单个支付信息退款
     */
    public function refundPrice(OrderPrice $price): void
    {
        // 支持退款，并且还没退款过的话，那就可以退款
        if (true === $price->isCanRefund() && false === $price->isRefund()) {
            $event = new BeforePriceRefundEvent();
            $event->setPrice($price);
            $event->setContract($price->getContract() ?? throw new ContractNotFoundException('Contract not found'));
            $this->eventDispatcher->dispatch($event);

            $price->setRefund(true);
            $this->entityManager->persist($price);
            $this->entityManager->flush();

            $event = new AfterPriceRefundEvent();
            $event->setPrice($price);
            $event->setContract($price->getContract() ?? throw new ContractNotFoundException('Contract not found'));
            $this->eventDispatcher->dispatch($event);
        }
    }

    /**
     * 取消订单行
     */
    public function cancelProduct(UserInterface $user, OrderProduct $product, ?string $cancelReason = null): void
    {
        $product->setValid(false);
        $product->setCancelTime(CarbonImmutable::now());
        $product->setCancelReason($cancelReason);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $contract = $product->getContract();
        if (null === $contract) {
            throw new ContractNotFoundException('Contract not found');
        }

        $allProductInvalid = true;
        foreach ($contract->getProducts() as $contractProduct) {
            if (true === $contractProduct->isValid()) {
                $allProductInvalid = false;
                break;
            }
        }
        if ($allProductInvalid) {
            $this->contractService->cancelOrder($contract, $user, $cancelReason);
        }
    }

    /**
     * @deprecated 发货功能已移到deliver-order-bundle
     * @param mixed $deliverOrder
     */
    public function sendExpress(Contract $contract, $deliverOrder): void
    {
        throw new FeatureMigratedException('发货', 'deliver-order-bundle');
    }

    /**
     * 发送发货通知
     * @deprecated 使用 DeliverOperationInterface::notifyShipment 替代
     */
    public function sendShipNotice(Contract $contract): bool
    {
        return $this->deliverOperation->notifyShipment($contract);
    }

    /**
     * 订单确认收货
     */
    #[Transactional]
    public function receiveOrder(Contract $order, ?UserInterface $user = null, ?CarbonInterface $now = null): void
    {
        $user ??= $order->getUser();
        $now ??= CarbonImmutable::now();

        $this->validateReceiveTime($order, $now);
        $this->updateOrderToReceived($order, $now);
        $this->processDeliverOrders($order, $user, $now);
        $this->processOrderProducts($order, $now);

        $this->entityManager->flush();
        $this->dispatchOrderReceivedEvent($order);
    }

    private function validateReceiveTime(Contract $order, CarbonInterface $now): void
    {
        if (null !== $order->getStartReceiveTime() && $now->lessThan($order->getStartReceiveTime())) {
            throw new OrderReceiveNotStartedException('未到开始收货时间');
        }

        if (null !== $order->getExpireReceiveTime() && $now->greaterThan($order->getExpireReceiveTime())) {
            throw new OrderReceiveExpiredException('订单已过期无法确认');
        }
    }

    private function updateOrderToReceived(Contract $order, CarbonInterface $now): void
    {
        $order->setState(OrderState::RECEIVED);
        $order->setFinishTime($now);
        $this->entityManager->persist($order);
    }

    private function processDeliverOrders(Contract $order, ?UserInterface $user, CarbonInterface $now): void
    {
        if ($this->deliverOperation->hasDeliveryRecords($order)) {
            $effectiveUser = $user ?? $order->getUser();
            if (null !== $effectiveUser) {
                $this->deliverOperation->markAllDeliveryAsReceived($order, $effectiveUser, $now);
            }
        }
    }

    private function processOrderProducts(Contract $order, CarbonInterface $now): void
    {
        foreach ($order->getProducts() as $product) {
            if (null !== $product->getFinishReceiveTime()) {
                continue;
            }

            $product->setFinishReceiveTime($now);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }

    private function dispatchOrderReceivedEvent(Contract $order): void
    {
        $event = new OrderReceivedEvent();
        $event->setContract($order);
        $this->eventDispatcher->dispatch($event);

        // TODO: Uncomment when Supplier entity is available
        // if ($order->getUser() !== null && $order->getSupplier() !== null) {
        //     $supplierUsers = $order->getSupplier()->getUsers()->filter(fn (UserInterface $element) => (bool) $element->isValid());
        //     foreach ($supplierUsers as $supplierUser) {
        //         $event = new SupplierOrderReceivedEvent();
        //         $event->setSender($order->getUser());
        //         $event->setReceiver($supplierUser);
        //         $event->setContract($order);
        //         $this->eventDispatcher->dispatch($event);
        //     }
        // }
    }

    /**
     * 记录订单状态
     *
     * @deprecated 逻辑移动到 \OrderCoreBundle\Service\ContractLogService::trackOrderState
     */
    public function trackOrderState(Contract $contract, ?OrderState $state = null): void
    {
        $log = new OrderLog();
        $log->setContract($contract);
        $log->setOrderSn($contract->getSn());
        $log->setCurrentState($state ?? $contract->getState());

        try {
            // TODO: Uncomment when DoctrineService is available
            // $this->doctrineService->asyncInsert($log);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('记录订单日志失败', [
                'exception' => $exception,
                'contract' => $contract,
            ]);
        }
    }

    /**
     * 标记发货单为已收货
     * @deprecated 此方法已迁移到 deliver-order-bundle，请使用 DeliverOperationInterface
     */
    public function makeDeliverOrderReceived(Contract $contract, UserInterface $user, \DateTimeInterface $now): bool
    {
        return $this->deliverOperation->markAllDeliveryAsReceived($contract, $user, $now);
    }

    /**
     * 将订单状态更新为售后成功
     */
    #[Transactional]
    public function updateOrderStatusToAftersalesSuccess(string $orderNumber): void
    {
        $contract = $this->contractRepository->findOneBy(['sn' => $orderNumber]);
        if (null === $contract) {
            throw new ContractNotFoundException("未找到订单: {$orderNumber}");
        }

        $currentState = $contract->getState();

        // 检查当前状态是否允许更新为售后成功
        if (OrderState::AFTERSALES_SUCCESS === $currentState) {
            $this->logger->info('订单状态已经是售后成功，无需更新', [
                'order_number' => $orderNumber,
                'current_state' => $currentState->value,
            ]);

            return;
        }

        // 验证订单状态是否可以更新为售后成功
        $allowedStates = [
            OrderState::PAID,
            OrderState::PART_SHIPPED,
            OrderState::SHIPPED,
            OrderState::RECEIVED,
            OrderState::AFTERSALES_ING,
        ];

        if (!in_array($currentState, $allowedStates, true)) {
            throw new \InvalidArgumentException("订单状态 '{$currentState->value}' 不允许更新为售后成功状态");
        }

        // 更新订单状态
        $oldState = $currentState;
        $contract->setState(OrderState::AFTERSALES_SUCCESS);
        $contract->setFinishTime(CarbonImmutable::now());

        $this->contractRepository->save($contract);

        $this->logger->info('订单状态已更新为售后成功', [
            'order_number' => $orderNumber,
            'old_state' => $oldState->value,
            'new_state' => OrderState::AFTERSALES_SUCCESS->value,
        ]);
    }
}
