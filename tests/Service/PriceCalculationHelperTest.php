<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Service\PriceCalculationHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PriceCalculationHelper::class)]
#[RunTestsInSeparateProcesses]
final class PriceCalculationHelperTest extends AbstractIntegrationTestCase
{
    private PriceCalculationHelper $priceCalculationHelper;

    public function testCalculateTotalWithValidPriceShouldReturnCorrectSum(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.50');
        $orderPrice->setTax('10.25');

        // Act
        $result = $this->priceCalculationHelper->calculateTotal($orderPrice);

        // Assert
        $this->assertSame(110.75, $result);
    }

    public function testCalculateTotalWithNullMoneyShouldTreatAsZero(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney(null);
        $orderPrice->setTax('5.50');

        // Act
        $result = $this->priceCalculationHelper->calculateTotal($orderPrice);

        // Assert
        $this->assertSame(5.5, $result);
    }

    public function testCalculateTotalWithNullTaxShouldTreatAsZero(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.00');
        $orderPrice->setTax(null);

        // Act
        $result = $this->priceCalculationHelper->calculateTotal($orderPrice);

        // Assert
        $this->assertSame(100.0, $result);
    }

    public function testCalculateTotalWithBothNullShouldReturnZero(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney(null);
        $orderPrice->setTax(null);

        // Act
        $result = $this->priceCalculationHelper->calculateTotal($orderPrice);

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testNormalizeToNumericStringWithNullShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(null);

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithIntegerShouldReturnString(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(42);

        // Assert
        $this->assertSame('42', $result);
    }

    public function testNormalizeToNumericStringWithFloatShouldReturnString(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(123.45);

        // Assert
        $this->assertSame('123.45', $result);
    }

    public function testNormalizeToNumericStringWithNumericStringShouldReturnSame(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString('999.99');

        // Assert
        $this->assertSame('999.99', $result);
    }

    public function testNormalizeToNumericStringWithZeroShouldReturnZeroString(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(0);

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithNegativeNumberShouldReturnNegativeString(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(-15.5);

        // Assert
        $this->assertSame('-15.5', $result);
    }

    public function testNormalizeToNumericStringWithNonNumericStringShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString('not_a_number');

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithEmptyStringShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString('');

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithBooleanTrueShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(true);

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithBooleanFalseShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(false);

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithArrayShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString([1, 2, 3]);

        // Assert
        $this->assertSame('0', $result);
    }

    public function testNormalizeToNumericStringWithObjectShouldReturnZero(): void
    {
        // Act
        $result = $this->priceCalculationHelper->normalizeToNumericString(new \stdClass());

        // Assert
        $this->assertSame('0', $result);
    }

    public function testFormatPriceWithTaxShouldReturnFormattedString(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.00');
        $orderPrice->setTax('8.50');
        $orderPrice->setCurrency('USD');

        // Act
        $result = $this->priceCalculationHelper->formatPriceWithTax($orderPrice);

        // Assert
        $this->assertSame('USD 108.50', $result);
    }

    public function testFormatPriceWithTaxWithNullValuesShouldHandleGracefully(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney(null);
        $orderPrice->setTax(null);
        $orderPrice->setCurrency('EUR');

        // Act
        $result = $this->priceCalculationHelper->formatPriceWithTax($orderPrice);

        // Assert
        $this->assertSame('EUR 0.00', $result);
    }

    public function testFormatPriceWithTaxShouldUseBcaddForPrecision(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('0.1');
        $orderPrice->setTax('0.2');
        $orderPrice->setCurrency('CNY');

        // Act
        $result = $this->priceCalculationHelper->formatPriceWithTax($orderPrice);

        // Assert
        $this->assertSame('CNY 0.30', $result);
    }

    public function testFormatPriceWithTaxWithLargeNumbersShouldMaintainPrecision(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('999999.99');
        $orderPrice->setTax('100000.01');
        $orderPrice->setCurrency('JPY');

        // Act
        $result = $this->priceCalculationHelper->formatPriceWithTax($orderPrice);

        // Assert
        $this->assertSame('JPY 1100000.00', $result);
    }

    protected function onSetUp(): void
    {
        $this->priceCalculationHelper = self::getService(PriceCalculationHelper::class);
    }
}
