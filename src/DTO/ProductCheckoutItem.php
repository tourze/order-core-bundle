<?php

declare(strict_types=1);

namespace OrderCoreBundle\DTO;

/**
 * 产品结账项 DTO
 */
readonly class ProductCheckoutItem
{
    /**
     * @param array<int, mixed> $attachments
     */
    public function __construct(
        private int $skuId,
        private int $quantity,
        private ?string $source = null,
        private array $attachments = [],
    ) {
    }

    public function getSkuId(): int
    {
        return $this->skuId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @return array<int, mixed>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * 从数组创建实例
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $skuIdValue = $data['skuId'] ?? 0;
        $quantityValue = $data['quantity'] ?? 0;
        $sourceValue = $data['source'] ?? null;
        $attachmentsValue = $data['attachments'] ?? [];

        $skuId = is_numeric($skuIdValue) ? (int) $skuIdValue : 0;
        $quantity = is_numeric($quantityValue) ? (int) $quantityValue : 0;
        $source = is_string($sourceValue) ? $sourceValue : null;

        /** @var array<int, mixed> $attachments */
        $attachments = is_array($attachmentsValue) ? array_values($attachmentsValue) : [];

        return new self($skuId, $quantity, $source, $attachments);
    }
}
