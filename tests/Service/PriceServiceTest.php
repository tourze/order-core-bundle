<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\Collection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Service\PriceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * @internal
 */
#[CoversClass(PriceService::class)]
#[RunTestsInSeparateProcesses]
final class PriceServiceTest extends AbstractIntegrationTestCase
{
    private PriceService $priceService;

    protected function onSetUp(): void
    {
        $this->priceService = self::getService(PriceService::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(PriceService::class, $this->priceService);
    }

    public function testFindFreightPriceBySkus(): void
    {
        $result = $this->priceService->findFreightPriceBySkus('freight-123', []);

        // 当前实现返回 null，未来可能根据业务需求返回实际运费
        $this->assertNull($result);
    }

    public function testGetOrderTotalPricesWithEmptyArray(): void
    {
        $result = $this->priceService->getOrderTotalPrices([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetOrderTotalTaxPricesWithEmptyArray(): void
    {
        $result = $this->priceService->getOrderTotalTaxPrices([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCalculateTotalPricesByType(): void
    {
        // 创建一个简单的Contract对象用于测试
        $contract = new Contract();

        // 测试不含税计算
        $result = $this->priceService->calculateTotalPricesByType($contract, false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sale', $result);
        $this->assertArrayHasKey('cost', $result);
        $this->assertArrayHasKey('compete', $result);
        $this->assertArrayHasKey('freight', $result);
        $this->assertArrayHasKey('marketing', $result);
        $this->assertArrayHasKey('original_price', $result);
        $this->assertArrayHasKey('total', $result);

        // 验证返回值为字符串类型而不是整数
        $this->assertIsString($result['sale']);
        $this->assertIsString($result['total']);
        $this->assertEquals('0', $result['sale']);   // 初始值，未经bcmath处理
        $this->assertEquals('0.00', $result['total']);  // 经过bcmath计算

        // 测试含税计算
        $resultWithTax = $this->priceService->calculateTotalPricesByType($contract, true);

        $this->assertIsArray($resultWithTax);
        $this->assertArrayHasKey('total', $resultWithTax);
        $this->assertIsString($resultWithTax['total']);
        $this->assertEquals('0.00', $resultWithTax['total']);
    }

    public function testCalculateTotalPricesByTypeWithDecimalAmount(): void
    {
        // 创建Contract对象
        $contract = new Contract();

        // 创建一个包含小数金额的OrderPrice
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('0.01');  // 小数金额
        $orderPrice->setTax('0.00');
        $orderPrice->setType(PriceType::SALE);
        $orderPrice->setName('测试商品');
        $orderPrice->setCurrency('CNY');

        // 使用反射添加价格到Contract（模拟数据库关联）
        $reflection = new \ReflectionClass($contract);
        $pricesProperty = $reflection->getProperty('prices');
        $pricesProperty->setAccessible(true);
        $collection = $pricesProperty->getValue($contract);
        if ($collection instanceof Collection) {
            $collection->add($orderPrice);
        }

        // 测试不含税计算 - 验证小数金额不会被截断为0
        $result = $this->priceService->calculateTotalPricesByType($contract, false);

        $this->assertIsString($result['sale']);
        $this->assertEquals('0.01', $result['sale']);
        $this->assertEquals('0.01', $result['total']);  // total = sale + freight - marketing

        // 测试含税计算
        $resultWithTax = $this->priceService->calculateTotalPricesByType($contract, true);

        $this->assertEquals('0.01', $resultWithTax['sale']);
        $this->assertEquals('0.01', $resultWithTax['total']);
    }
}
