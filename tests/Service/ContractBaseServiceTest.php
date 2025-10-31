<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\ContractBaseService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractBaseService::class)]
#[RunTestsInSeparateProcesses]
final class ContractBaseServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化
    }

    public function testCanBeInstantiated(): void
    {
        $service = self::getService(ContractBaseService::class);
        $this->assertInstanceOf(ContractBaseService::class, $service);
    }

    public function testHasRequiredMethods(): void
    {
        $service = self::getService(ContractBaseService::class);

        $this->assertTrue(method_exists($service, 'createOrder'));
        $this->assertTrue(method_exists($service, 'payOrder'));
        $this->assertTrue(method_exists($service, 'cancelOrder'));
    }

    public function testCreateOrderMethodExists(): void
    {
        $service = self::getService(ContractBaseService::class);
        $this->assertTrue(method_exists($service, 'createOrder'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(ContractBaseService::class, 'createOrder');
        $this->assertTrue($reflection->isPublic());
    }

    public function testPayOrderMethodExists(): void
    {
        $service = self::getService(ContractBaseService::class);
        $this->assertTrue(method_exists($service, 'payOrder'));
    }

    public function testCancelOrderMethodExists(): void
    {
        $service = self::getService(ContractBaseService::class);
        $this->assertTrue(method_exists($service, 'cancelOrder'));
    }
}
