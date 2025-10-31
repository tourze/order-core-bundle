<?php

namespace OrderCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Entity\OrderProduct;

class ContractMapperService
{
    public function __construct(
        private readonly ContractPriceService $priceService,
        private readonly ContractDisplayService $displayService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCheckoutArray(Contract $contract): array
    {
        return [
            'id' => $contract->getId(),
            'currencyPrices' => $this->priceService->getCurrencyPrices($contract),
            'appendPrices' => $this->mapAppendPricesToArray($contract),
            'displayPrice' => $this->priceService->getDisplayPrice($contract),
            'displayTaxPrice' => $this->priceService->getDisplayTaxPrice($contract),
            'freightPrices' => $this->priceService->getFreightPrices($contract),
            'payable' => isset($this->priceService->getPayPrices($contract)['CNY']) && $this->priceService->getPayPrices($contract)['CNY'] > 0,
            'needConsignee' => $this->displayService->isNeedConsignee($contract),
            'contacts' => $this->mapContactsToArray($contract->getContacts()),
            'products' => $this->mapProductsToArray($contract->getProducts()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapPlainArray(Contract $contract): array
    {
        return [
            'id' => $contract->getId(),
            'sn' => $contract->getSn(),
            'remark' => $contract->getRemark(),
            'supplier' => null,
            'supplierAcceptTime' => null,
            'supplierRejectTime' => null,
            'createTime' => $contract->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $contract->getUpdateTime()?->format('Y-m-d H:i:s'),
            'createUser' => null,
            'updateUser' => null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapAppendPricesToArray(Contract $contract): array
    {
        $appendPrices = [];
        foreach ($this->displayService->getAppendPrices($contract) as $appendPrice) {
            if (method_exists($appendPrice, 'retrieveCheckoutArray')) {
                $appendPrices[] = $appendPrice->retrieveCheckoutArray();
            }
        }

        return $appendPrices;
    }

    /**
     * @param Collection<int, OrderContact> $contacts
     * @return array<int, array<string, mixed>>
     */
    private function mapContactsToArray(Collection $contacts): array
    {
        $result = [];
        foreach ($contacts as $contact) {
            $result[] = $contact->retrieveCheckoutArray();
        }

        return $result;
    }

    /**
     * @param Collection<int, OrderProduct> $products
     * @return array<int, array<string, mixed>>
     */
    private function mapProductsToArray(Collection $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $result[] = $product->retrieveCheckoutArray();
        }

        return $result;
    }
}
