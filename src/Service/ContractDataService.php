<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;

class ContractDataService
{
    public function __construct(
        private readonly ?DeliveryDataService $deliveryDataService = null,
    ) {
    }

    public function getContactPerson(Contract $contract): string
    {
        return $this->extractContactField($contract, 'getRealname');
    }

    public function getContactPhone(Contract $contract): string
    {
        return $this->extractContactField($contract, 'getMobile');
    }

    public function getContactAddress(Contract $contract): string
    {
        return $this->extractContactField($contract, 'getAddress');
    }

    public function getSpuGtin(Contract $contract): string
    {
        return $this->extractProductField($contract, function (OrderProduct $product): ?string {
            $spu = $product->getSpu();
            if (null === $spu) {
                return null;
            }
            $gtin = $spu->getGtin();

            return is_string($gtin) ? $gtin : null;
        });
    }

    public function getSpuId(Contract $contract): string
    {
        return $this->extractProductField($contract, function (OrderProduct $product): ?string {
            $spu = $product->getSpu();
            if (null === $spu) {
                return null;
            }

            return (string) $spu->getId();
        });
    }

    public function exportSkuId(Contract $contract): string
    {
        return $this->extractProductField($contract, function (OrderProduct $product): ?string {
            $sku = $product->getSku();
            if (null === $sku) {
                return null;
            }

            return $sku->getId();
        });
    }

    public function getOpenId(Contract $contract): string
    {
        return null !== $contract->getUser() ? $contract->getUser()->getUserIdentifier() : '';
    }

    public function getUserUnionId(Contract $contract): ?string
    {
        if (null !== $contract->getUser()) {
            return '';
        }

        return '';
    }

    public function getOrderUserId(Contract $contract): string
    {
        if (null !== $contract->getUser()) {
            return $contract->getUser()->getUserIdentifier();
        }

        return '';
    }

    public function getProductQuantity(Contract $contract): int
    {
        return $this->sumProductQuantities($contract, function (OrderProduct $product): int {
            return $product->getQuantity();
        });
    }

    public function getValidProductQuantity(Contract $contract): int
    {
        return $this->sumProductQuantities($contract, function (OrderProduct $product): int {
            $isValid = $product->isValid();
            if (null === $isValid) {
                return 0;
            }

            return $isValid ? $product->getQuantity() : 0;
        });
    }

    public function getDeliverFirstTime(Contract $contract): ?\DateTimeInterface
    {
        return $this->deliveryDataService?->getDeliverFirstTime($contract);
    }

    public function getDeliverLastTime(Contract $contract): ?\DateTimeInterface
    {
        return $this->deliveryDataService?->getDeliverLastTime($contract);
    }

    public function getDeliverQuantity(Contract $contract): int
    {
        return $this->deliveryDataService?->getDeliverQuantity($contract) ?? 0;
    }

    public function getReceivedQuantity(Contract $contract): int
    {
        if (null === $this->deliveryDataService) {
            return 0;
        }

        return $this->deliveryDataService->getDeliverCompleted($contract) ? $this->deliveryDataService->getDeliverQuantity($contract) : 0;
    }

    private function extractContactField(Contract $contract, string $method): string
    {
        $arr = [];
        foreach ($contract->getContacts() as $contact) {
            switch ($method) {
                case 'getRealname':
                    $arr[] = $contact->getRealname();
                    break;
                case 'getMobile':
                    $arr[] = $contact->getMobile();
                    break;
                case 'getAddress':
                    $arr[] = $contact->getAddress();
                    break;
            }
        }

        return implode(' ', $arr);
    }

    private function extractProductField(Contract $contract, callable $extractor): string
    {
        $values = [];
        foreach ($contract->getProducts() as $product) {
            $value = $extractor($product);
            if (null !== $value) {
                $values[] = $value;
            }
        }

        return implode(' ', $values);
    }

    private function sumProductQuantities(Contract $contract, callable $quantityExtractor): int
    {
        $total = 0;
        foreach ($contract->getProducts() as $product) {
            $quantity = $quantityExtractor($product);
            if (is_int($quantity)) {
                $total += $quantity;
            }
        }

        return $total;
    }
}
