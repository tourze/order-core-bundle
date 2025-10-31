<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Statistics;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Statistics\RevenueMetricProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * @internal
 */
#[CoversClass(RevenueMetricProvider::class)]
final class RevenueMetricProviderTest extends TestCase
{
    /** @var Connection&MockObject */
    private MockObject $connection;

    private RevenueMetricProvider $provider;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->provider = new RevenueMetricProvider($this->connection);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(RevenueMetricProvider::class, $this->provider);
    }

    public function testImplementsMetricProviderInterface(): void
    {
        $this->assertInstanceOf(MetricProviderInterface::class, $this->provider);
    }

    public function testGetMetricId(): void
    {
        $this->assertSame('total_revenue', $this->provider->getMetricId());
    }

    public function testGetMetricName(): void
    {
        $this->assertSame('收入金额', $this->provider->getMetricName());
    }

    public function testGetMetricDescription(): void
    {
        $this->assertSame('当日总收入金额', $this->provider->getMetricDescription());
    }

    public function testGetMetricUnit(): void
    {
        $this->assertSame('元', $this->provider->getMetricUnit());
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
        $totalRevenue = 1234.56;

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['total_revenue' => $totalRevenue])
        ;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with(
                self::stringContains('SELECT COALESCE(SUM(CAST(op.money AS DECIMAL(20,2))), 0) as total_revenue'),
                self::callback(function (array $params) use ($date) {
                    return $params['startDate'] === $date->startOfDay()->format('Y-m-d H:i:s')
                        && $params['endDate'] === $date->endOfDay()->format('Y-m-d H:i:s')
                        && $params['paidStates'] === [
                            OrderState::PAID->value,
                            OrderState::PART_SHIPPED->value,
                            OrderState::SHIPPED->value,
                            OrderState::RECEIVED->value,
                        ];
                }),
                self::callback(function (array $types) {
                    return ArrayParameterType::STRING === $types['paidStates'];
                })
            )
            ->willReturn($result)
        ;

        $metricValue = $this->provider->getMetricValue($date);

        // 验证结果转换为分（整数）
        $this->assertSame(123456, $metricValue); // 1234.56 * 100
    }

    public function testGetMetricValueWithZeroRevenue(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['total_revenue' => 0])
        ;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $metricValue = $this->provider->getMetricValue($date);

        $this->assertSame(0, $metricValue);
    }

    public function testGetMetricValueWithNullRevenue(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(['total_revenue' => null])
        ;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $metricValue = $this->provider->getMetricValue($date);

        $this->assertSame(0, $metricValue);
    }

    public function testGetMetricValueExecutesCorrectSql(): void
    {
        $date = CarbonImmutable::create(2024, 3, 15, 14, 30, 45);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['total_revenue' => 100.00]);

        $expectedSql = <<<'SQL'
            SELECT COALESCE(SUM(CAST(op.money AS DECIMAL(20,2))), 0) as total_revenue
            FROM order_contract_price op
            JOIN order_contract_order o ON op.contract_id = o.id
            WHERE o.create_time BETWEEN :startDate AND :endDate
            AND o.state IN (:paidStates)
            AND op.currency = 'CNY'
            AND op.paid = TRUE
            SQL;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->equalTo($expectedSql),
                self::anything(),
                self::anything()
            )
            ->willReturn($result)
        ;

        $this->provider->getMetricValue($date);
    }

    public function testGetMetricValueUsesCorrectParameters(): void
    {
        $date = CarbonImmutable::create(2024, 6, 20, 15, 30, 0);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['total_revenue' => 500.25]);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with(
                self::anything(),
                [
                    'startDate' => '2024-06-20 00:00:00',
                    'endDate' => '2024-06-20 23:59:59',
                    'paidStates' => [
                        OrderState::PAID->value,
                        OrderState::PART_SHIPPED->value,
                        OrderState::SHIPPED->value,
                        OrderState::RECEIVED->value,
                    ],
                ],
                ['paidStates' => ArrayParameterType::STRING]
            )
            ->willReturn($result)
        ;

        $metricValue = $this->provider->getMetricValue($date);

        $this->assertSame(50025, $metricValue); // 500.25 * 100
    }

    public function testGetMetricValueUsesCorrectPaidStates(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['total_revenue' => 0]);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with(
                self::anything(),
                self::callback(function (array $params) {
                    $expectedStates = [
                        OrderState::PAID->value,
                        OrderState::PART_SHIPPED->value,
                        OrderState::SHIPPED->value,
                        OrderState::RECEIVED->value,
                    ];

                    return $params['paidStates'] === $expectedStates;
                }),
                self::anything()
            )
            ->willReturn($result)
        ;

        $this->provider->getMetricValue($date);
    }

    public function testGetMetricValueWithDifferentDates(): void
    {
        $testCases = [
            ['date' => CarbonImmutable::create(2024, 1, 1), 'revenue' => 1000.50, 'expected' => 100050],
            ['date' => CarbonImmutable::create(2024, 6, 15), 'revenue' => 2500.75, 'expected' => 250075],
            ['date' => CarbonImmutable::create(2024, 12, 31), 'revenue' => 5000.00, 'expected' => 500000],
        ];

        foreach ($testCases as $case) {
            // 为每个测试用例创建新的provider和mock
            $connection = $this->createMock(Connection::class);
            $provider = new RevenueMetricProvider($connection);

            $result = $this->createMock(Result::class);
            $result->method('fetchAssociative')->willReturn(['total_revenue' => $case['revenue']]);

            $connection
                ->expects($this->once())
                ->method('executeQuery')
                ->willReturn($result)
            ;

            $this->assertNotNull($case['date'], 'Test case date cannot be null');
            $metricValue = $provider->getMetricValue($case['date']);

            $this->assertSame($case['expected'], $metricValue);
        }
    }

    public function testGetMetricValuePreservesOriginalDate(): void
    {
        $originalDate = CarbonImmutable::create(2024, 5, 20, 15, 30, 45);
        $this->assertNotNull($originalDate, 'Failed to create test date');
        $dateBeforeCall = $originalDate->copy();

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['total_revenue' => 100]);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $this->provider->getMetricValue($originalDate);

        // 验证原始日期对象没有被修改
        $this->assertTrue($originalDate->equalTo($dateBeforeCall));
    }

    public function testConstructorAcceptsConnection(): void
    {
        $connection = $this->createMock(Connection::class);
        $provider = new RevenueMetricProvider($connection);

        $this->assertInstanceOf(RevenueMetricProvider::class, $provider);
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

    public function testGetMetricValueReturnsInteger(): void
    {
        $date = CarbonImmutable::create(2024, 1, 15);
        $this->assertNotNull($date, 'Failed to create test date');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(['total_revenue' => 123.45]);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($result)
        ;

        $metricValue = $this->provider->getMetricValue($date);

        $this->assertIsInt($metricValue);
        $this->assertSame(12345, $metricValue);
    }
}
