<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;

class DeliveryDataService
{
    public function __construct(
        private readonly DeliverOperationInterface $deliverOperation,
    ) {
    }

    /**
     * 获取合同的首次发货时间
     */
    public function getDeliverFirstTime(Contract $contract): ?\DateTimeInterface
    {
        return $this->deliverOperation->getContractFirstDeliveryTime($contract);
    }

    /**
     * 获取合同的最后发货时间
     */
    public function getDeliverLastTime(Contract $contract): ?\DateTimeInterface
    {
        return $this->deliverOperation->getContractLastDeliveryTime($contract);
    }

    /**
     * 获取合同的已发货数量
     * 不考虑并发 - 此方法为查询方法，不涉及数据修改
     */
    public function getDeliverQuantity(Contract $contract): int
    {
        return $this->deliverOperation->getContractDeliveredQuantity($contract);
    }

    /**
     * 获取合同是否全部发货
     */
    public function getDeliverCompleted(Contract $contract): bool
    {
        return $this->deliverOperation->isContractFullyDelivered($contract);
    }

    /**
     * 获取订单产品的已发货数量
     * 不考虑并发 - 此方法为查询方法，不涉及数据修改
     */
    public function getOrderProductDeliverQuantity(OrderProduct $orderProduct): int
    {
        return $this->deliverOperation->getOrderProductDeliveredQuantity($orderProduct);
    }

    /**
     * 获取订单产品的首次发货时间
     */
    public function getOrderProductDeliverFirstTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        return $this->deliverOperation->getOrderProductFirstDeliveryTime($orderProduct);
    }

    /**
     * 获取订单产品的最后发货时间
     */
    public function getOrderProductDeliverLastTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        return $this->deliverOperation->getOrderProductLastDeliveryTime($orderProduct);
    }
}
