<?php

namespace OrderCoreBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\DTO\DeliveryOrderDTO;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 空实现的发货操作服务 - 当 deliver-order-bundle 不可用时的默认实现
 */
#[WithMonologChannel(channel: 'order_core')]
#[Autoconfigure(public: true)]
class NullDeliverOperation implements DeliverOperationInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function notifyShipment(Contract $contract): bool
    {
        $this->logger->info('发货通知已忽略 - deliver-order-bundle 未启用', [
            'contract_id' => $contract->getId(),
            'contract_sn' => $contract->getSn(),
        ]);

        return false;
    }

    public function hasDeliveryRecords(Contract $contract): bool
    {
        return false;
    }

    public function markAllDeliveryAsReceived(Contract $contract, UserInterface $user, \DateTimeInterface $now): bool
    {
        $this->logger->info('发货确认收货已忽略 - deliver-order-bundle 未启用', [
            'contract_id' => $contract->getId(),
            'user_id' => $user->getUserIdentifier(),
        ]);

        return false;
    }

    /**
     * @return array{shipped_count: int, total_count: int, all_received: bool}
     */
    public function getDeliveryStatusSummary(Contract $contract): array
    {
        return [
            'shipped_count' => 0,
            'total_count' => 0,
            'all_received' => false,
        ];
    }

    public function getDeliveryOrdersByContract(Contract $contract): array
    {
        return [];
    }

    public function getContractFirstDeliveryTime(Contract $contract): ?\DateTimeInterface
    {
        return null;
    }

    public function getContractLastDeliveryTime(Contract $contract): ?\DateTimeInterface
    {
        return null;
    }

    public function getContractDeliveredQuantity(Contract $contract): int
    {
        return 0;
    }

    public function isContractFullyDelivered(Contract $contract): bool
    {
        return false;
    }

    public function getOrderProductDeliveredQuantity(OrderProduct $orderProduct): int
    {
        return 0;
    }

    public function getOrderProductFirstDeliveryTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        return null;
    }

    public function getOrderProductLastDeliveryTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        return null;
    }

    public function getDeliveryOrderById(int $deliveryOrderId): ?DeliveryOrderDTO
    {
        return null;
    }

    /**
     * @return array{status: string, data: array<mixed>, message: string}
     */
    public function getExpressTrackingData(int $deliveryOrderId): array
    {
        return [
            'status' => '0',
            'data' => [],
            'message' => 'deliver-order-bundle 未启用',
        ];
    }
}
