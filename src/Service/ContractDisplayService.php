<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;

class ContractDisplayService
{
    public function __construct(
        private readonly ?OrderProductDeliveryService $orderProductDeliveryService = null,
    ) {
    }

    public function getStatusText(Contract $contract): string
    {
        return match ($contract->getState()) {
            OrderState::PAYING, OrderState::INIT => '待付款',
            OrderState::PAID => '待发货',
            OrderState::SHIPPED => '待收货',
            OrderState::EXPIRED => '订单异常',
            default => $contract->getState()->getLabel(),
        };
    }

    public function getDeliverState(Contract $contract): ?string
    {
        $totalQuantity = 0;
        $deliveredQuantity = 0;
        $receivedQuantity = 0;

        foreach ($contract->getProducts() as $product) {
            if (true === $product->isValid()) {
                $totalQuantity += $product->getQuantity();
                $deliveredQuantity += $this->orderProductDeliveryService?->getDeliverQuantity($product) ?? 0;
                $receivedQuantity += $this->orderProductDeliveryService?->getReceivedQuantity($product) ?? 0;
            }
        }

        if (0 === $totalQuantity) {
            return null;
        }

        if ($receivedQuantity >= $totalQuantity) {
            return '已完成';
        }
        if ($receivedQuantity > 0) {
            return '部分收货';
        }
        if ($deliveredQuantity >= $totalQuantity) {
            return '已发货';
        }
        if ($deliveredQuantity > 0) {
            return '部分发货';
        }

        return '待发货';
    }

    public function getSupplierAuditStatus(Contract $contract): bool
    {
        return false;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function renderContracts(Contract $contract): array
    {
        $contacts = [];
        foreach ($contract->getContacts() as $contact) {
            $contacts[] = [
                'realname' => $contact->getRealname(),
                'mobile' => $contact->getMobile(),
                'address' => $contact->getAddress(),
                'provinceName' => $contact->getProvinceName(),
                'cityName' => $contact->getCityName(),
                'areaName' => $contact->getAreaName(),
                'idCard' => $contact->getIdCard(),
            ];
        }

        return $contacts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function renderProducts(Contract $contract): array
    {
        $products = [];
        foreach ($contract->getProducts() as $product) {
            $products[] = [
                'id' => $product->getId(),
                'spu' => null !== $product->getSpu() ? [
                    'id' => $product->getSpu()->getId(),
                    'title' => $product->getSpu()->getTitle(),
                    'gtin' => $this->getGtinValue($product->getSpu()),
                ] : null,
                'sku' => null !== $product->getSku() ? [
                    'id' => $product->getSku()->getId(),
                    'name' => $product->getSku()->getFullName(),
                    'unit' => $product->getSku()->getUnit(),
                ] : null,
                'quantity' => $product->getQuantity(),
                'price' => $product->getDisplayPrice(),
                'currency' => 'CNY',
            ];
        }

        return $products;
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toSelectItem(Contract $contract): array
    {
        return [
            'label' => $contract->getSn(),
            'text' => $contract->getSn(),
            'value' => $contract->getId(),
        ];
    }

    /**
     * @return array<int, OrderPrice>
     */
    public function getAppendPrices(Contract $contract): array
    {
        $result = [];
        foreach ($contract->getPrices() as $price) {
            if (null !== $price->getProduct()) {
                continue;
            }

            $result[] = $price;
        }

        return $result;
    }

    public function isNeedConsignee(Contract $contract): bool
    {
        $res = false;
        foreach ($contract->getProducts() as $product) {
            if (true === $product->getSku()?->isNeedConsignee()) {
                $res = true;
                break;
            }
        }

        return $res;
    }

    /**
     * @param iterable<OrderProduct|OrderPrice|OrderContact> $items
     * @return array<int, array<string, mixed>>
     */
    public function mapToCheckoutArray(iterable $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!is_object($item) || !method_exists($item, 'retrieveCheckoutArray')) {
                continue;
            }
            $result[] = $item->retrieveCheckoutArray();
        }

        return $result;
    }

    private function getGtinValue(object $spu): ?string
    {
        try {
            $reflectionClass = new \ReflectionClass($spu);
            if ($reflectionClass->hasMethod('getGtin')) {
                $method = $reflectionClass->getMethod('getGtin');
                $result = $method->invoke($spu);

                return is_string($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
        }

        return null;
    }
}
