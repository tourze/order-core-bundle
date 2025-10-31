<?php

namespace OrderCoreBundle\DTO;

/**
 * 发货库存数据传输对象 - 解耦 order-core-bundle 与 deliver-order-bundle
 */
class DeliveryStockDTO
{
    public function __construct(
        private readonly int $id,
        private readonly string $skuCode,
        private readonly int $quantity,
        private readonly string $status = 'pending',
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSkuCode(): string
    {
        return $this->skuCode;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isReceived(): bool
    {
        return 'received' === $this->status;
    }
}
