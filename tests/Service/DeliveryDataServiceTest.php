<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\DeliveryDataService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(DeliveryDataService::class)]
#[RunTestsInSeparateProcesses]
final class DeliveryDataServiceTest extends AbstractIntegrationTestCase
{
    private DeliveryDataService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(DeliveryDataService::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(DeliveryDataService::class, $this->service);
    }
}
