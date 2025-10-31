<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\ProductCoreServiceWrapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ProductCoreServiceWrapper::class)]
#[RunTestsInSeparateProcesses]
final class ProductCoreServiceWrapperTest extends AbstractIntegrationTestCase
{
    private ProductCoreServiceWrapper $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(ProductCoreServiceWrapper::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(ProductCoreServiceWrapper::class, $this->service);
    }

    public function testServiceHasRequiredMethods(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);

        // 验证所有重要的方法都存在
        $this->assertTrue($reflectionClass->hasMethod('findSkuById'));
        $this->assertTrue($reflectionClass->hasMethod('findSpuById'));
        $this->assertTrue($reflectionClass->hasMethod('safeFindValidSkuById'));
        $this->assertTrue($reflectionClass->hasMethod('safeGetSkus'));
        $this->assertTrue($reflectionClass->hasMethod('safeIncreaseSalesReal'));
        $this->assertTrue($reflectionClass->hasMethod('safeIsValid'));
    }

    public function testSafeFindValidSkuByIdHasCorrectSignature(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);
        $method = $reflectionClass->getMethod('safeFindValidSkuById');

        $this->assertEquals(2, $method->getNumberOfRequiredParameters());
    }

    public function testSafeGetSkusHasCorrectSignature(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);
        $method = $reflectionClass->getMethod('safeGetSkus');

        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testSafeIncreaseSalesRealHasCorrectSignature(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);
        $method = $reflectionClass->getMethod('safeIncreaseSalesReal');

        $this->assertEquals(3, $method->getNumberOfRequiredParameters());
    }

    public function testSafeIsValidHasCorrectSignature(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);
        $method = $reflectionClass->getMethod('safeIsValid');

        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testFindSkuByIdReturnsSkuOrNull(): void
    {
        // 测试不存在的SKU ID
        $result = $this->service->findSkuById('nonexistent-sku');
        $this->assertNull($result, 'findSkuById应该为不存在的ID返回null');
    }

    public function testFindSpuByIdReturnsSpuOrNull(): void
    {
        // 测试不存在的SPU ID
        $result = $this->service->findSpuById('nonexistent-spu');
        $this->assertNull($result, 'findSpuById应该为不存在的ID返回null');
    }
}
