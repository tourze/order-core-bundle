<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Statistics;

use Carbon\CarbonImmutable;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Statistics\OrderCountMetricProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use StatisticsBundle\Metric\MetricProviderInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(OrderCountMetricProvider::class)]
#[RunTestsInSeparateProcesses]
final class OrderCountMetricProviderTest extends AbstractIntegrationTestCase
{
    private OrderCountMetricProvider $provider;
    private ContractRepository $contractRepository;

    protected function onSetUp(): void
    {
        $this->provider = self::getService(OrderCountMetricProvider::class);
        $this->contractRepository = self::getService(ContractRepository::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(OrderCountMetricProvider::class, $this->provider);
    }

    public function testImplementsMetricProviderInterface(): void
    {
        $this->assertInstanceOf(MetricProviderInterface::class, $this->provider);
    }

    public function testGetMetricId(): void
    {
        $this->assertSame('total_order_count', $this->provider->getMetricId());
    }

    public function testGetMetricName(): void
    {
        $this->assertSame('订单数量', $this->provider->getMetricName());
    }

    public function testGetMetricDescription(): void
    {
        $this->assertSame('当日订单总数', $this->provider->getMetricDescription());
    }

    public function testGetMetricUnit(): void
    {
        $this->assertSame('单', $this->provider->getMetricUnit());
    }

    public function testGetCategory(): void
    {
        $this->assertSame('平台订单', $this->provider->getCategory());
    }

    public function testGetCategoryOrder(): void
    {
        $this->assertSame(20, $this->provider->getCategoryOrder());
    }

    public function testGetMetricValueWithNoOrders(): void
    {
        // 查询一个未来日期，确保没有订单
        $date = CarbonImmutable::create(2099, 12, 31);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->provider->getMetricValue($date);

        $this->assertSame(0, $result);
    }

    public function testGetMetricValueWithOrders(): void
    {
        // 创建测试订单
        $targetDate = CarbonImmutable::now();

        $contract1 = new Contract();
        $contract1->setState(OrderState::INIT);
        $contract1->setCreateTime($targetDate->toDateTimeImmutable());
        $this->contractRepository->save($contract1);

        $contract2 = new Contract();
        $contract2->setState(OrderState::INIT);
        $contract2->setCreateTime($targetDate->toDateTimeImmutable());
        $this->contractRepository->save($contract2);

        // 获取指标值
        $result = $this->provider->getMetricValue($targetDate);

        // 应该至少有2个订单（测试创建的）
        $this->assertGreaterThanOrEqual(2, $result);
    }

    public function testGetMetricValueFiltersDateRange(): void
    {
        // 在不同日期创建订单
        $today = CarbonImmutable::now();
        $yesterday = $today->subDay();

        // 创建昨天的订单
        $yesterdayContract = new Contract();
        $yesterdayContract->setState(OrderState::INIT);
        $yesterdayContract->setCreateTime($yesterday->toDateTimeImmutable());
        $this->contractRepository->save($yesterdayContract);

        // 创建今天的订单
        $todayContract = new Contract();
        $todayContract->setState(OrderState::INIT);
        $todayContract->setCreateTime($today->toDateTimeImmutable());
        $this->contractRepository->save($todayContract);

        // 获取今天的计数
        $todayCount = $this->provider->getMetricValue($today);
        // 获取昨天的计数
        $yesterdayCount = $this->provider->getMetricValue($yesterday);

        // 两个计数应该都至少为1
        $this->assertGreaterThanOrEqual(1, $todayCount);
        $this->assertGreaterThanOrEqual(1, $yesterdayCount);
    }

    public function testAllMetricInterfaceMethodsReturnExpectedTypes(): void
    {
        $this->assertIsString($this->provider->getMetricId());
        $this->assertIsString($this->provider->getMetricName());
        $this->assertIsString($this->provider->getMetricDescription());
        $this->assertIsString($this->provider->getMetricUnit());
        $this->assertIsString($this->provider->getCategory());
        $this->assertIsInt($this->provider->getCategoryOrder());
    }
}
