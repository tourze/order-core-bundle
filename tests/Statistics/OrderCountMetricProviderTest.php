<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Statistics;

use Carbon\CarbonImmutable;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Statistics\OrderCountMetricProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * @internal
 */
#[CoversClass(OrderCountMetricProvider::class)]
final class OrderCountMetricProviderTest extends TestCase
{
    /** @var ContractRepository&MockObject */
    private MockObject $contractRepository;

    private OrderCountMetricProvider $provider;

    protected function setUp(): void
    {
        $this->contractRepository = $this->getMockBuilder(ContractRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['countByCreateTimeDateRange'])
            ->getMock()
        ;
        $this->provider = new OrderCountMetricProvider($this->contractRepository);
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

    public function testGetMetricValue(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15, 12, 0, 0);
        $this->assertNotNull($date, 'Failed to create test date');
        $expectedCount = 42;

        $this->contractRepository
            ->expects($this->once())
            ->method('countByCreateTimeDateRange')
            ->with(
                self::callback(function (\DateTimeInterface $startDate) use ($date) {
                    return $startDate->format('Y-m-d H:i:s') === $date->startOfDay()->format('Y-m-d H:i:s');
                }),
                self::callback(function (\DateTimeInterface $endDate) use ($date) {
                    return $endDate->format('Y-m-d H:i:s') === $date->endOfDay()->format('Y-m-d H:i:s');
                })
            )
            ->willReturn($expectedCount)
        ;

        $result = $this->provider->getMetricValue($date);

        $this->assertSame($expectedCount, $result);
    }

    public function testGetMetricValueWithDifferentDates(): void
    {
        $testCases = [
            ['date' => CarbonImmutable::create(2024, 1, 1), 'count' => 10],
            ['date' => CarbonImmutable::create(2024, 6, 15), 'count' => 25],
            ['date' => CarbonImmutable::create(2024, 12, 31), 'count' => 50],
        ];

        // 验证所有测试日期都成功创建
        foreach ($testCases as $case) {
            $this->assertNotNull($case['date'], 'Failed to create test date');
        }

        foreach ($testCases as $case) {
            // 为每个测试用例创建新的repository和provider
            $contractRepository = $this->getMockBuilder(ContractRepository::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['countByCreateTimeDateRange'])
                ->getMock()
            ;

            $provider = new OrderCountMetricProvider($contractRepository);

            $contractRepository
                ->expects($this->once())
                ->method('countByCreateTimeDateRange')
                ->willReturn($case['count'])
            ;

            $this->assertNotNull($case['date'], 'Test case date cannot be null');
            $result = $provider->getMetricValue($case['date']);

            $this->assertSame($case['count'], $result);
        }
    }

    public function testGetMetricValueWithZeroCount(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15);
        $this->assertNotNull($date, 'Failed to create test date');

        $this->contractRepository
            ->expects($this->once())
            ->method('countByCreateTimeDateRange')
            ->willReturn(0)
        ;

        $result = $this->provider->getMetricValue($date);

        $this->assertSame(0, $result);
    }

    public function testGetMetricValueCallsRepositoryWithCorrectDateRange(): void
    {
        $date = CarbonImmutable::create(2024, 3, 15, 14, 30, 45);
        $this->assertNotNull($date, 'Failed to create test date');
        $expectedStartOfDay = $date->startOfDay(); // 2024-03-15 00:00:00
        $expectedEndOfDay = $date->endOfDay();     // 2024-03-15 23:59:59

        $this->contractRepository
            ->expects($this->once())
            ->method('countByCreateTimeDateRange')
            ->with(
                self::callback(function (\DateTimeInterface $startDate) use ($expectedStartOfDay) {
                    return $startDate->format('Y-m-d H:i:s') === $expectedStartOfDay->format('Y-m-d H:i:s');
                }),
                self::callback(function (\DateTimeInterface $endDate) use ($expectedEndOfDay) {
                    return $endDate->format('Y-m-d H:i:s') === $expectedEndOfDay->format('Y-m-d H:i:s');
                })
            )
            ->willReturn(15)
        ;

        $this->provider->getMetricValue($date);
    }

    public function testGetMetricValueWithDifferentTimeZones(): void
    {
        // 测试不同时区的日期处理
        $date = CarbonImmutable::create(2024, 6, 15, 12, 0, 0, 'Asia/Shanghai');
        $this->assertNotNull($date, 'Failed to create test date with timezone');

        $this->contractRepository
            ->expects($this->once())
            ->method('countByCreateTimeDateRange')
            ->with(
                self::callback(function (\DateTimeInterface $startDate) {
                    $time = $startDate->format('H:i:s');

                    return '00:00:00' === $time;
                }),
                self::callback(function (\DateTimeInterface $endDate) {
                    $time = $endDate->format('H:i:s');

                    return '23:59:59' === $time;
                })
            )
            ->willReturn(8)
        ;

        $result = $this->provider->getMetricValue($date);

        $this->assertSame(8, $result);
    }

    public function testGetMetricValuePreservesOriginalDate(): void
    {
        $originalDate = CarbonImmutable::create(2024, 5, 20, 15, 30, 45);
        $this->assertNotNull($originalDate, 'Failed to create test date');
        $dateBeforeCall = $originalDate->copy();

        $this->contractRepository
            ->expects($this->once())
            ->method('countByCreateTimeDateRange')
            ->willReturn(12)
        ;

        $this->provider->getMetricValue($originalDate);

        // 验证原始日期对象没有被修改
        $this->assertTrue($originalDate->equalTo($dateBeforeCall));
    }

    public function testConstructorAcceptsContractRepository(): void
    {
        $repository = $this->createMock(ContractRepository::class);
        $provider = new OrderCountMetricProvider($repository);

        $this->assertInstanceOf(OrderCountMetricProvider::class, $provider);
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
