<?php

declare(strict_types=1);

namespace OrderCoreBundle\Service;

use ReflectionClass;
use ReflectionException;

/**
 * 产品核心服务反射包装器
 *
 * 用于安全调用外部依赖包中可能不存在的方法
 */
class ProductCoreServiceWrapper
{
    public function __construct(
        private readonly ?object $skuService = null,
        private readonly ?object $spuService = null,
    ) {
    }

    /**
     * 安全调用 SkuService::increaseSalesReal 方法
     *
     * 并发处理策略：不考虑并发 - SkuService::increaseSalesReal 使用数据库层面的原子UPDATE操作，
     * 数据库原生支持并发控制，不需要应用层额外加锁
     *
     * 注意：此方法涉及并发敏感操作，在调用时需要确保：
     * 1. 在事务中执行
     * 2. 使用适当的锁机制防止重复增加销量
     * 3. 考虑使用乐观锁或悲观锁保证数据一致性
     * 4. 确保幂等性，防止重复执行
     */
    public function safeIncreaseSalesReal(object $skuService, string $skuId, int $quantity): void
    {
        try {
            $reflection = new \ReflectionClass($skuService);
            if ($reflection->hasMethod('increaseSalesReal')) {
                $method = $reflection->getMethod('increaseSalesReal');
                $method->invoke($skuService, $skuId, $quantity);
            }
            // 方法不存在时记录日志或执行备用逻辑
            // 这里我们静默处理，因为这不是关键业务逻辑
        } catch (\ReflectionException $e) {
            // 反射调用失败时静默处理
        }
    }

    /**
     * 安全调用 SkuService::findValidSkuById 方法
     */
    public function safeFindValidSkuById(object $skuService, string $skuId): ?object
    {
        try {
            $reflection = new \ReflectionClass($skuService);
            if ($reflection->hasMethod('findValidSkuById')) {
                $method = $reflection->getMethod('findValidSkuById');
                $result = $method->invoke($skuService, $skuId);

                return is_object($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
            // 反射调用失败时返回 null
        }

        // 方法不存在时返回 null
        return null;
    }

    /**
     * 安全调用 Spu::isValid 方法
     */
    public function safeIsValid(object $spu): ?bool
    {
        try {
            $reflection = new \ReflectionClass($spu);
            if ($reflection->hasMethod('isValid')) {
                $method = $reflection->getMethod('isValid');
                $result = $method->invoke($spu);

                return is_bool($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
            // 反射调用失败时返回 null
        }

        // 方法不存在时返回 null，让调用者决定如何处理
        return null;
    }

    /**
     * 安全调用 Spu::getSkus 方法
     */
    public function safeGetSkus(object $spu): ?object
    {
        try {
            $reflection = new \ReflectionClass($spu);
            if ($reflection->hasMethod('getSkus')) {
                $method = $reflection->getMethod('getSkus');
                $result = $method->invoke($spu);

                return is_object($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
            // 反射调用失败时返回 null
        }

        // 方法不存在时返回 null
        return null;
    }

    /**
     * 通过SkuService查找SKU
     */
    public function findSkuById(string $skuId): ?object
    {
        if (null === $this->skuService) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($this->skuService);
            if ($reflection->hasMethod('findValidSkuById')) {
                $method = $reflection->getMethod('findValidSkuById');
                $result = $method->invoke($this->skuService, $skuId);

                return is_object($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
            // 反射调用失败时返回 null
        }

        return null;
    }

    /**
     * 通过SpuService查找SPU
     */
    public function findSpuById(string $spuId): ?object
    {
        if (null === $this->spuService) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($this->spuService);
            if ($reflection->hasMethod('findValidSpuById')) {
                $method = $reflection->getMethod('findValidSpuById');
                $result = $method->invoke($this->spuService, $spuId);

                return is_object($result) ? $result : null;
            }
        } catch (\ReflectionException $e) {
            // 反射调用失败时返回 null
        }

        return null;
    }
}
