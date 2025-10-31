<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\OrderProductDeliveryService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(OrderProductDeliveryService::class)]
#[RunTestsInSeparateProcesses]
final class OrderProductDeliveryServiceTest extends AbstractIntegrationTestCase
{
    private OrderProductDeliveryService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(OrderProductDeliveryService::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(OrderProductDeliveryService::class, $this->service);
    }
}
