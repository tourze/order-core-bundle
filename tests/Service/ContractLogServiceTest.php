<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\ContractLogService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractLogService::class)]
#[RunTestsInSeparateProcesses]
final class ContractLogServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化
    }

    public function testServiceIsInstantiable(): void
    {
        $contractLogService = self::getService(ContractLogService::class);
        $this->assertInstanceOf(ContractLogService::class, $contractLogService);
    }

    public function testHasRequiredMethods(): void
    {
        $contractLogService = self::getService(ContractLogService::class);

        $this->assertTrue(method_exists($contractLogService, 'createOrder'));
        $this->assertTrue(method_exists($contractLogService, 'payOrder'));
        $this->assertTrue(method_exists($contractLogService, 'cancelOrder'));
        $this->assertTrue(method_exists($contractLogService, 'trackOrderState'));
    }

    public function testCreateOrderMethodExists(): void
    {
        $contractLogService = self::getService(ContractLogService::class);
        $this->assertTrue(method_exists($contractLogService, 'createOrder'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(ContractLogService::class, 'createOrder');
        $this->assertTrue($reflection->isPublic());
    }

    public function testPayOrderMethodExists(): void
    {
        $contractLogService = self::getService(ContractLogService::class);
        $this->assertTrue(method_exists($contractLogService, 'payOrder'));
    }

    public function testCancelOrderMethodExists(): void
    {
        $contractLogService = self::getService(ContractLogService::class);
        $this->assertTrue(method_exists($contractLogService, 'cancelOrder'));
    }

    public function testTrackOrderStateMethodExists(): void
    {
        $contractLogService = self::getService(ContractLogService::class);
        $this->assertTrue(method_exists($contractLogService, 'trackOrderState'));
    }
}
