<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\DTO\DeliveryOrderDTO;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 发货操作接口 - order-core-bundle 与 deliver-order-bundle 的解耦接口
 */
interface DeliverOperationInterface
{
    /**
     * 发送发货通知
     * 替代原有的 sendShipNotice 方法
     */
    public function notifyShipment(Contract $contract): bool;

    /**
     * 检查订单是否有发货记录
     */
    public function hasDeliveryRecords(Contract $contract): bool;

    /**
     * 标记订单的所有发货记录为已收货
     */
    public function markAllDeliveryAsReceived(Contract $contract, UserInterface $user, \DateTimeInterface $now): bool;

    /**
     * 获取订单的发货状态摘要
     * 返回 ['shipped_count' => int, 'total_count' => int, 'all_received' => bool]
     * @return array{shipped_count: int, total_count: int, all_received: bool}
     */
    public function getDeliveryStatusSummary(Contract $contract): array;

    // 新增方法 - 支持 DeliveryDataService 的功能

    /**
     * 获取合同关联的发货单
     *
     * @return DeliveryOrderDTO[]
     */
    public function getDeliveryOrdersByContract(Contract $contract): array;

    /**
     * 获取合同的首次发货时间
     */
    public function getContractFirstDeliveryTime(Contract $contract): ?\DateTimeInterface;

    /**
     * 获取合同的最后发货时间
     */
    public function getContractLastDeliveryTime(Contract $contract): ?\DateTimeInterface;

    /**
     * 获取合同的已发货数量
     */
    public function getContractDeliveredQuantity(Contract $contract): int;

    /**
     * 获取合同是否全部发货
     */
    public function isContractFullyDelivered(Contract $contract): bool;

    /**
     * 获取订单产品的已发货数量
     */
    public function getOrderProductDeliveredQuantity(OrderProduct $orderProduct): int;

    /**
     * 获取订单产品的首次发货时间
     */
    public function getOrderProductFirstDeliveryTime(OrderProduct $orderProduct): ?\DateTimeInterface;

    /**
     * 获取订单产品的最后发货时间
     */
    public function getOrderProductLastDeliveryTime(OrderProduct $orderProduct): ?\DateTimeInterface;

    // 新增方法 - 支持发货单查询功能

    /**
     * 根据发货单ID获取发货单信息
     */
    public function getDeliveryOrderById(int $deliveryOrderId): ?DeliveryOrderDTO;

    /**
     * 获取快递查询数据
     * 返回格式: ['status' => string, 'data' => array, 'message' => string]
     * @return array{status: string, data: array<mixed>, message: string}
     */
    public function getExpressTrackingData(int $deliveryOrderId): array;
}
