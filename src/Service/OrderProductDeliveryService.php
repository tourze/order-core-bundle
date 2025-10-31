<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\OrderProduct;

class OrderProductDeliveryService
{
    public function __construct(
        private readonly DeliveryDataService $deliveryDataService,
    ) {
    }

    /**
     * 获取订单商品的已发货数量
     * 不考虑并发 - 此方法为查询代理，底层服务已做并发处理
     */
    public function getDeliverQuantity(OrderProduct $orderProduct): int
    {
        return $this->deliveryDataService->getOrderProductDeliverQuantity($orderProduct);
    }

    /**
     * 获取订单商品的已收货数量
     * 不考虑并发 - 此方法为查询代理，底层服务已做并发处理
     */
    public function getReceivedQuantity(OrderProduct $orderProduct): int
    {
        // 简化处理：如果有发货记录且发货时间距今超过7天，认为已收货
        $deliverQuantity = $this->getDeliverQuantity($orderProduct);
        $lastDeliverTime = $this->deliveryDataService->getOrderProductDeliverLastTime($orderProduct);

        if ($deliverQuantity > 0 && null !== $lastDeliverTime) {
            $now = new \DateTimeImmutable();
            $interval = $now->diff($lastDeliverTime);
            if ($interval->days >= 7) {
                return $deliverQuantity;
            }
        }

        return 0;
    }

    /**
     * 获取订单商品的最近发货时间
     */
    public function getLastDeliverTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        return $this->deliveryDataService->getOrderProductDeliverLastTime($orderProduct);
    }

    /**
     * 获取订单商品的最近收货时间
     */
    public function getLastReceivedTime(OrderProduct $orderProduct): ?\DateTimeInterface
    {
        // 简化处理：收货时间为发货时间+7天
        $lastDeliverTime = $this->getLastDeliverTime($orderProduct);
        if (null !== $lastDeliverTime && $lastDeliverTime instanceof \DateTimeImmutable) {
            return $lastDeliverTime->modify('+7 days');
        }

        return null;
    }
}
