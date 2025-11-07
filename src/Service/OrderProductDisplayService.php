<?php

declare(strict_types=1);

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Repository\OrderProductRepository;

/**
 * 订单商品显示服务
 * 提供订单详情页商品分类显示的便利方法
 */
class OrderProductDisplayService
{
    public function __construct(
        private readonly OrderProductRepository $orderProductRepository,
    ) {
    }

    /**
     * 获取订单的商品分组数据（用于订单详情页显示）
     *
     * @return array{
     *     normal: array<int, array<string, mixed>>,
     *     gifts: array<int, array<string, mixed>>,
     *     redeems: array<int, array<string, mixed>>
     * }
     */
    public function getOrderProductsGrouped(Contract $contract): array
    {
        $groupedProducts = $this->orderProductRepository->findByContractGroupedBySource($contract->getId());
        
        return [
            'normal' => $this->convertProductsToArray($groupedProducts['normal'] ?? []),
            'gifts' => $this->convertProductsToArray($groupedProducts['coupon_gift'] ?? []),
            'redeems' => $this->convertProductsToArray($groupedProducts['coupon_redeem'] ?? []),
        ];
    }

    /**
     * 获取订单的正常购买商品
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNormalPurchaseProducts(Contract $contract): array
    {
        $products = $this->orderProductRepository->findByContractAndSource($contract->getId(), 'normal');
        return $this->convertProductsToArray($products);
    }

    /**
     * 获取订单的赠品
     *
     * @return array<int, array<string, mixed>>
     */
    public function getGiftProducts(Contract $contract): array
    {
        $products = $this->orderProductRepository->findByContractAndSource($contract->getId(), 'coupon_gift');
        return $this->convertProductsToArray($products);
    }

    /**
     * 获取订单的兑换券商品
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRedeemProducts(Contract $contract): array
    {
        $products = $this->orderProductRepository->findByContractAndSource($contract->getId(), 'coupon_redeem');
        return $this->convertProductsToArray($products);
    }

    /**
     * 检查订单是否包含赠品
     */
    public function hasGifts(Contract $contract): bool
    {
        $giftsCount = $this->orderProductRepository->count([
            'contract' => $contract->getId(),
            'source' => 'coupon_gift'
        ]);
        
        return $giftsCount > 0;
    }

    /**
     * 检查订单是否包含兑换券商品
     */
    public function hasRedeems(Contract $contract): bool
    {
        $redeemsCount = $this->orderProductRepository->count([
            'contract' => $contract->getId(),
            'source' => 'coupon_redeem'
        ]);
        
        return $redeemsCount > 0;
    }

    /**
     * 获取订单商品统计信息
     *
     * @return array{
     *     total: int,
     *     normal: int,
     *     gifts: int,
     *     redeems: int
     * }
     */
    public function getOrderProductStats(Contract $contract): array
    {
        $groupedProducts = $this->orderProductRepository->findByContractGroupedBySource($contract->getId());
        
        return [
            'total' => array_sum(array_map('count', $groupedProducts)),
            'normal' => count($groupedProducts['normal'] ?? []),
            'gifts' => count($groupedProducts['coupon_gift'] ?? []),
            'redeems' => count($groupedProducts['coupon_redeem'] ?? []),
        ];
    }

    /**
     * 将商品实体数组转换为API数组
     *
     * @param array<\OrderCoreBundle\Entity\OrderProduct> $products
     * @return array<int, array<string, mixed>>
     */
    private function convertProductsToArray(array $products): array
    {
        return array_map(
            static fn($product) => $product->retrieveCheckoutArray(),
            $products
        );
    }
}