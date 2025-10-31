<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\PriceFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PriceFormatter::class)]
final class PriceFormatterTest extends TestCase
{
    private PriceFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new PriceFormatter();

        // 清除环境变量，确保测试独立性
        unset($_ENV['DISPLAY_FREE_PRICE']);
    }

    protected function tearDown(): void
    {
        // 清理环境变量
        unset($_ENV['DISPLAY_FREE_PRICE']);
    }

    public function testConstructorWithCustomFreeLabelShouldUseCustomLabel(): void
    {
        // Arrange & Act
        $formatter = new PriceFormatter('自定义免费');

        // Assert - 通过formatOrDefault测试构造函数设置的label
        $result = $formatter->formatOrDefault('');
        $this->assertEquals('自定义免费', $result);
    }

    public function testConstructorWithoutParameterShouldUseDefaultLabel(): void
    {
        // Arrange & Act
        $formatter = new PriceFormatter();

        // Assert
        $result = $formatter->formatOrDefault('');
        $this->assertEquals('免费', $result);
    }

    public function testConstructorWithEnvVariableShouldUseEnvValue(): void
    {
        // Arrange
        $_ENV['DISPLAY_FREE_PRICE'] = '环境变量免费';

        // Act
        $formatter = new PriceFormatter();

        // Assert
        $result = $formatter->formatOrDefault('');
        $this->assertEquals('环境变量免费', $result);
    }

    public function testFormatCurrencyPricesWithSingleCurrencyShouldFormatCorrectly(): void
    {
        // Arrange
        $prices = ['USD' => 99.50];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals('99.50USD', $result);
    }

    public function testFormatCurrencyPricesWithMultipleCurrenciesShouldJoinWithPlus(): void
    {
        // Arrange
        $prices = [
            'USD' => 99.50,
            'EUR' => 85.75,
            'CNY' => 680.00,
        ];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals('99.50USD+85.75EUR+680.00CNY', $result);
    }

    public function testFormatCurrencyPricesWithZeroAmountsShouldSkipZeros(): void
    {
        // Arrange
        $prices = [
            'USD' => 99.50,
            'EUR' => 0.00,
            'CNY' => 680.00,
        ];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals('99.50USD+680.00CNY', $result);
    }

    public function testFormatCurrencyPricesWithAllZeroAmountsShouldReturnEmpty(): void
    {
        // Arrange
        $prices = [
            'USD' => 0.00,
            'EUR' => 0.00,
        ];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals('', $result);
    }

    public function testFormatCurrencyPricesWithEmptyArrayShouldReturnEmpty(): void
    {
        // Arrange
        $prices = [];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals('', $result);
    }

    #[DataProvider('numberFormatProvider')]
    public function testFormatCurrencyPricesWithVariousNumbersShouldFormatCorrectly(float $amount, string $expected): void
    {
        // Arrange
        $prices = ['USD' => $amount];

        // Act
        $result = $this->formatter->formatCurrencyPrices($prices);

        // Assert
        $this->assertEquals($expected . 'USD', $result);
    }

    /** @return array<string, array{float, string}> */
    public static function numberFormatProvider(): array
    {
        return [
            '整数' => [100.0, '100.00'],
            '小数点一位' => [99.5, '99.50'],
            '小数点两位' => [99.99, '99.99'],
            '小数点多位' => [99.999, '100.00'], // 四舍五入
            '很小的数' => [0.01, '0.01'],
            '大数' => [9999.99, '9999.99'],
        ];
    }

    public function testFormatOrDefaultWithNonEmptyStringShouldReturnOriginal(): void
    {
        // Arrange
        $formatted = '99.50USD+85.75EUR';

        // Act
        $result = $this->formatter->formatOrDefault($formatted);

        // Assert
        $this->assertEquals($formatted, $result);
    }

    public function testFormatOrDefaultWithEmptyStringShouldReturnFreeLabel(): void
    {
        // Arrange
        $formatted = '';

        // Act
        $result = $this->formatter->formatOrDefault($formatted);

        // Assert
        $this->assertEquals('免费', $result);
    }

    public function testFormatOrDefaultWithEmptyStringAndEnvVariableShouldUseEnvValue(): void
    {
        // Arrange
        $_ENV['DISPLAY_FREE_PRICE'] = '测试免费';
        $formatted = '';

        // Act
        $result = $this->formatter->formatOrDefault($formatted);

        // Assert
        $this->assertEquals('测试免费', $result);
    }

    public function testFormatOrDefaultWithCustomConstructorAndEnvVariableShouldPrioritizeEnv(): void
    {
        // Arrange - 环境变量应该覆盖构造函数参数
        $_ENV['DISPLAY_FREE_PRICE'] = '环境变量优先';
        $formatter = new PriceFormatter('构造函数设置');
        $formatted = '';

        // Act
        $result = $formatter->formatOrDefault($formatted);

        // Assert
        $this->assertEquals('环境变量优先', $result);
    }

    public function testFormatUnitPriceWithValidInputShouldCalculateCorrectly(): void
    {
        // Arrange
        $amount = 100.00;
        $currency = 'USD';
        $quantity = 4;

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals('25.00USD', $result);
    }

    public function testFormatUnitPriceWithZeroQuantityShouldReturnEmpty(): void
    {
        // Arrange
        $amount = 100.00;
        $currency = 'USD';
        $quantity = 0;

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals('', $result);
    }

    public function testFormatUnitPriceWithNegativeQuantityShouldReturnEmpty(): void
    {
        // Arrange
        $amount = 100.00;
        $currency = 'USD';
        $quantity = -1;

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals('', $result);
    }

    public function testFormatUnitPriceWithZeroAmountShouldReturnEmpty(): void
    {
        // Arrange
        $amount = 0.00;
        $currency = 'USD';
        $quantity = 5;

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals('', $result);
    }

    public function testFormatUnitPriceWithNegativeAmountShouldReturnEmpty(): void
    {
        // Arrange
        $amount = -50.00;
        $currency = 'USD';
        $quantity = 2;

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals('', $result);
    }

    #[DataProvider('unitPriceProvider')]
    public function testFormatUnitPriceWithVariousInputsShouldCalculateCorrectly(float $amount, int $quantity, string $expected): void
    {
        // Arrange
        $currency = 'USD';

        // Act
        $result = $this->formatter->formatUnitPrice($amount, $currency, $quantity);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /** @return array<string, array{float, int, string}> */
    public static function unitPriceProvider(): array
    {
        return [
            '完美整除' => [100.00, 4, '25.00USD'],
            '不能整除' => [100.00, 3, '33.33USD'],
            '小数金额' => [99.99, 2, '50.00USD'], // 四舍五入
            '单个商品' => [25.50, 1, '25.50USD'],
            '大数量' => [1000.00, 100, '10.00USD'],
            '小金额大数量' => [1.00, 3, '0.33USD'],
        ];
    }

    public function testCompleteWorkflowShouldWorkCorrectly(): void
    {
        // Arrange - 完整的使用场景
        $_ENV['DISPLAY_FREE_PRICE'] = '暂时免费';

        $prices = [
            'USD' => 299.99,
            'CNY' => 0.00,    // 会被过滤掉
            'EUR' => 85.50,
        ];

        // Act
        $formatted = $this->formatter->formatCurrencyPrices($prices);
        $finalResult = $this->formatter->formatOrDefault($formatted);
        $unitPrice = $this->formatter->formatUnitPrice(299.99, 'USD', 3);

        // Assert
        $this->assertEquals('299.99USD+85.50EUR', $formatted);
        $this->assertEquals('299.99USD+85.50EUR', $finalResult);
        $this->assertEquals('100.00USD', $unitPrice);
    }

    public function testCompleteWorkflowWithZeroPricesShouldShowFreeLabel(): void
    {
        // Arrange
        $_ENV['DISPLAY_FREE_PRICE'] = '全部免费';

        $prices = [
            'USD' => 0.00,
            'CNY' => 0.00,
        ];

        // Act
        $formatted = $this->formatter->formatCurrencyPrices($prices);
        $finalResult = $this->formatter->formatOrDefault($formatted);

        // Assert
        $this->assertEquals('', $formatted);
        $this->assertEquals('全部免费', $finalResult);
    }
}
