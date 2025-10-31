<?php

namespace OrderCoreBundle;

use CounterBundle\CounterBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use OrderLimitBundle\OrderLimitBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineAsyncInsertBundle\DoctrineAsyncInsertBundle;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\FreightTemplateBundle\FreightTemplateBundle;
use Tourze\JsonRPCLockBundle\JsonRPCLockBundle;
use Tourze\JsonRPCSecurityBundle\JsonRPCSecurityBundle;
use Tourze\OrderCheckoutBundle\OrderCheckoutBundle;
use Tourze\ProductCoreBundle\ProductCoreBundle;
use Tourze\ProductLimitRuleBundle\ProductLimitRuleBundle;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;
use Tourze\Symfony\CronJob\CronJobBundle;
use Tourze\TempFileBundle\TempFileBundle;

/**
 * @see https://symfony.com/doc/current/bundles/prepend_extension.html
 */
class OrderCoreBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineSnowflakeBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineIndexedBundle::class => ['all' => true],
            CronJobBundle::class => ['all' => true],
            JsonRPCLockBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            DoctrineAsyncInsertBundle::class => ['all' => true],
            HttpClientBundle::class => ['all' => true],
            OrderCheckoutBundle::class => ['all' => true],
            OrderLimitBundle::class => ['all' => true],
            ProductCoreBundle::class => ['all' => true],
            ProductLimitRuleBundle::class => ['all' => true],
            TempFileBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            FreightTemplateBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
            JsonRPCSecurityBundle::class => ['all' => true],
            CounterBundle::class => ['all' => true],
        ];
    }
}
