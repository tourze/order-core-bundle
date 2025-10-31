<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\ContractEventService;
use OrderCoreBundle\Service\ContractService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractEventService::class)]
#[RunTestsInSeparateProcesses]
final class ContractEventServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化
    }

    public function testServiceCanBeInstantiated(): void
    {
        $contractEventService = self::getService(ContractEventService::class);
        $this->assertInstanceOf(ContractEventService::class, $contractEventService);
    }

    public function testServiceImplementsContractServiceInterface(): void
    {
        $contractEventService = self::getService(ContractEventService::class);
        $this->assertInstanceOf(ContractService::class, $contractEventService);
    }

    public function testHasRequiredMethods(): void
    {
        $contractEventService = self::getService(ContractEventService::class);

        $this->assertTrue(method_exists($contractEventService, 'createOrder'));
        $this->assertTrue(method_exists($contractEventService, 'payOrder'));
        $this->assertTrue(method_exists($contractEventService, 'cancelOrder'));
    }

    public function testCreateOrderMethodExists(): void
    {
        $contractEventService = self::getService(ContractEventService::class);
        $this->assertTrue(method_exists($contractEventService, 'createOrder'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(ContractEventService::class, 'createOrder');
        $this->assertTrue($reflection->isPublic());
    }

    public function testPayOrderMethodExists(): void
    {
        $contractEventService = self::getService(ContractEventService::class);
        $this->assertTrue(method_exists($contractEventService, 'payOrder'));
    }

    public function testCancelOrderMethodExists(): void
    {
        $contractEventService = self::getService(ContractEventService::class);
        $this->assertTrue(method_exists($contractEventService, 'cancelOrder'));
    }
}
