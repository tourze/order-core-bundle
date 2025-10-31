<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use OrderCoreBundle\OrderCoreBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\ProductCoreBundle\ProductCoreBundle;

/**
 * @internal
 */
#[CoversClass(OrderCoreBundle::class)]
#[RunTestsInSeparateProcesses]
final class OrderCoreBundleTest extends AbstractBundleTestCase
{
    public function testBundleDependenciesContainsAllRequiredBundles(): void
    {
        $dependencies = OrderCoreBundle::getBundleDependencies();

        $this->assertArrayHasKey(DoctrineSnowflakeBundle::class, $dependencies);
        $this->assertArrayHasKey(ProductCoreBundle::class, $dependencies);
        $this->assertArrayHasKey(SecurityBundle::class, $dependencies);
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
    }
}
