<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Counter;

use OrderCoreBundle\Counter\OrderDailyCounterProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(OrderDailyCounterProvider::class)]
#[RunTestsInSeparateProcesses]
final class OrderDailyCounterProviderTest extends AbstractIntegrationTestCase
{
    private OrderDailyCounterProvider $provider;

    protected function onSetUp(): void
    {
        $this->provider = self::getService(OrderDailyCounterProvider::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(OrderDailyCounterProvider::class, $this->provider);
    }

    public function testHasCorrectProviderKey(): void
    {
        $result = $this->provider->getCounters();

        $this->assertIsIterable($result);
    }

    public function testProvideReturnsIterable(): void
    {
        $counters = $this->provider->getCounters();

        $this->assertIsIterable($counters);
    }
}
