<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\PriceAggregator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PriceAggregator::class)]
final class PriceAggregatorTest extends TestCase
{
    private PriceAggregator $aggregator;

    protected function setUp(): void
    {
        $this->aggregator = new PriceAggregator();
    }

    public function testAddPriceWithSingleCurrencyShouldAccumulateCorrectly(): void
    {
        // Arrange & Act
        $this->aggregator->addPrice('USD', 100.00, 10.00);
        $this->aggregator->addPrice('USD', 50.00, 5.00);

        // Assert
        $totals = $this->aggregator->getCurrencyTotals();
        $expected = [
            'USD' => ['money' => 150.00, 'tax' => 15.00],
        ];

        $this->assertEquals($expected, $totals);
    }

    public function testAddPriceWithMultipleCurrenciesShouldKeepSeparate(): void
    {
        // Arrange & Act
        $this->aggregator->addPrice('USD', 100.00, 10.00);
        $this->aggregator->addPrice('CNY', 680.00, 68.00);
        $this->aggregator->addPrice('EUR', 85.00, 8.50);

        // Assert
        $totals = $this->aggregator->getCurrencyTotals();
        $expected = [
            'USD' => ['money' => 100.00, 'tax' => 10.00],
            'CNY' => ['money' => 680.00, 'tax' => 68.00],
            'EUR' => ['money' => 85.00, 'tax' => 8.50],
        ];

        $this->assertEquals($expected, $totals);
    }

    public function testAddPriceWithZeroTaxShouldDefaultToZero(): void
    {
        // Arrange & Act
        $this->aggregator->addPrice('USD', 100.00);

        // Assert
        $totals = $this->aggregator->getCurrencyTotals();
        $expected = [
            'USD' => ['money' => 100.00, 'tax' => 0.00],
        ];

        $this->assertEquals($expected, $totals);
    }

    public function testAddPriceWithNegativeValuesShouldAccumulateCorrectly(): void
    {
        // Arrange & Act - 模拟折扣场景
        $this->aggregator->addPrice('USD', 100.00, 10.00);
        $this->aggregator->addPrice('USD', -20.00, -2.00);

        // Assert
        $totals = $this->aggregator->getCurrencyTotals();
        $expected = [
            'USD' => ['money' => 80.00, 'tax' => 8.00],
        ];

        $this->assertEquals($expected, $totals);
    }

    public function testGetTotalByCurrencyWithExistingCurrencyShouldReturnSum(): void
    {
        // Arrange
        $this->aggregator->addPrice('USD', 100.00, 15.00);

        // Act
        $total = $this->aggregator->getTotalByCurrency('USD');

        // Assert
        $this->assertEquals(115.00, $total);
    }

    public function testGetTotalByCurrencyWithNonExistentCurrencyShouldReturnZero(): void
    {
        // Arrange - empty aggregator

        // Act
        $total = $this->aggregator->getTotalByCurrency('EUR');

        // Assert
        $this->assertEquals(0.00, $total);
    }

    public function testGetMoneyByCurrencyWithExistingCurrencyShouldReturnMoneyOnly(): void
    {
        // Arrange
        $this->aggregator->addPrice('USD', 100.00, 15.00);

        // Act
        $money = $this->aggregator->getMoneyByCurrency('USD');

        // Assert
        $this->assertEquals(100.00, $money);
    }

    public function testGetMoneyByCurrencyWithNonExistentCurrencyShouldReturnZero(): void
    {
        // Arrange - empty aggregator

        // Act
        $money = $this->aggregator->getMoneyByCurrency('EUR');

        // Assert
        $this->assertEquals(0.00, $money);
    }

    public function testIsEmptyWithNoDataShouldReturnTrue(): void
    {
        // Arrange - fresh aggregator

        // Act
        $isEmpty = $this->aggregator->isEmpty();

        // Assert
        $this->assertTrue($isEmpty);
    }

    public function testIsEmptyWithDataShouldReturnFalse(): void
    {
        // Arrange
        $this->aggregator->addPrice('USD', 100.00);

        // Act
        $isEmpty = $this->aggregator->isEmpty();

        // Assert
        $this->assertFalse($isEmpty);
    }

    #[DataProvider('floatPrecisionProvider')]
    public function testAddPriceWithFloatPrecisionShouldHandleCorrectly(float $money, float $tax, float $expectedMoney, float $expectedTax): void
    {
        // Arrange & Act
        $this->aggregator->addPrice('USD', $money, $tax);

        // Assert
        $totals = $this->aggregator->getCurrencyTotals();
        $this->assertEquals($expectedMoney, $totals['USD']['money']);
        $this->assertEquals($expectedTax, $totals['USD']['tax']);
    }

    /** @return array<string, array{float, float, float, float}> */
    public static function floatPrecisionProvider(): array
    {
        return [
            'small decimals' => [0.01, 0.001, 0.01, 0.001],
            'large numbers' => [99999.99, 9999.99, 99999.99, 9999.99],
            'zero values' => [0.00, 0.00, 0.00, 0.00],
            'mixed precision' => [123.456, 12.34, 123.456, 12.34],
        ];
    }

    public function testComplexAggregationScenarioShouldCalculateCorrectly(): void
    {
        // Arrange & Act - 复杂的真实业务场景
        // 商品价格
        $this->aggregator->addPrice('USD', 299.99, 30.00);
        $this->aggregator->addPrice('USD', 199.50, 20.00);

        // 运费
        $this->aggregator->addPrice('USD', 15.00, 1.50);

        // 折扣
        $this->aggregator->addPrice('USD', -50.00, -5.00);

        // CNY商品
        $this->aggregator->addPrice('CNY', 1980.00, 198.00);

        // Assert
        $usdTotal = $this->aggregator->getTotalByCurrency('USD');
        $cnyTotal = $this->aggregator->getTotalByCurrency('CNY');

        $this->assertEquals(510.99, $usdTotal); // (299.99 + 199.50 + 15.00 - 50.00) + (30.00 + 20.00 + 1.50 - 5.00)
        $this->assertEquals(2178.00, $cnyTotal); // 1980.00 + 198.00

        $this->assertFalse($this->aggregator->isEmpty());
        $this->assertEquals(464.49, $this->aggregator->getMoneyByCurrency('USD'));
        $this->assertEquals(1980.00, $this->aggregator->getMoneyByCurrency('CNY'));
    }
}
