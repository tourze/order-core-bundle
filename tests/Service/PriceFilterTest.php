<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Service\PriceFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * @internal
 */
#[CoversClass(PriceFilter::class)]
final class PriceFilterTest extends TestCase
{
    public function testIsFreightPriceWithFreightNameShouldReturnTrue(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn('运费');
        $price->method('getType')->willReturn(PriceType::SALE);

        // Act
        $result = PriceFilter::isFreightPrice($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsFreightPriceWithFreightTypeShouldReturnTrue(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn('商品价格');
        $price->method('getType')->willReturn(PriceType::FREIGHT);

        // Act
        $result = PriceFilter::isFreightPrice($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsFreightPriceWithBothFreightNameAndTypeShouldReturnTrue(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn('运费');
        $price->method('getType')->willReturn(PriceType::FREIGHT);

        // Act
        $result = PriceFilter::isFreightPrice($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsFreightPriceWithNeitherFreightNameNorTypeShouldReturnFalse(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn('商品价格');
        $price->method('getType')->willReturn(PriceType::SALE);

        // Act
        $result = PriceFilter::isFreightPrice($price);

        // Assert
        $this->assertFalse($result);
    }

    #[DataProvider('freightNameProvider')]
    public function testIsFreightPriceWithVariousNamesShouldMatchOnlyFreight(string $name, bool $expected): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn($name);
        $price->method('getType')->willReturn(PriceType::SALE);

        // Act
        $result = PriceFilter::isFreightPrice($price);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /** @return array<string, array{string, bool}> */
    public static function freightNameProvider(): array
    {
        return [
            '完全匹配运费' => ['运费', true],
            '商品价格' => ['商品价格', false],
            '优惠券' => ['优惠券', false],
            '配送费' => ['配送费', false],
            '包含运费的字符串' => ['包含运费的名称', false], // 只精确匹配
            '空字符串' => ['', false],
            '服务费' => ['服务费', false],
        ];
    }

    public function testHasProductWithExistingProductShouldReturnTrue(): void
    {
        // Arrange
        $product = $this->createMock(OrderProduct::class);
        $price = $this->createOrderPriceMock();
        $price->method('getProduct')->willReturn($product);

        // Act
        $result = PriceFilter::hasProduct($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testHasProductWithNullProductShouldReturnFalse(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getProduct')->willReturn(null);

        // Act
        $result = PriceFilter::hasProduct($price);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsPaidWithTrueShouldReturnTrue(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('isPaid')->willReturn(true);

        // Act
        $result = PriceFilter::isPaid($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsPaidWithFalseShouldReturnFalse(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('isPaid')->willReturn(false);

        // Act
        $result = PriceFilter::isPaid($price);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsPaidWithNullShouldReturnFalse(): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('isPaid')->willReturn(null);

        // Act
        $result = PriceFilter::isPaid($price);

        // Assert
        $this->assertFalse($result);
    }

    #[DataProvider('positiveAmountProvider')]
    public function testIsPositiveWithPositiveAmountShouldReturnTrue(?string $money, ?string $tax): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getMoney')->willReturn($money);
        $price->method('getTax')->willReturn($tax);

        // Act
        $result = PriceFilter::isPositive($price);

        // Assert
        $this->assertTrue($result);
    }

    /** @return array<string, array{?string, ?string}> */
    public static function positiveAmountProvider(): array
    {
        return [
            '仅金额为正' => ['100.50', null],
            '仅税费为正' => [null, '10.00'],
            '金额税费都为正' => ['100.00', '15.00'],
            '金额为零税费为正' => ['0', '5.00'],
            '金额为正税费为零' => ['50.00', '0'],
            '小数金额' => ['0.01', null],
            '小数税费' => [null, '0.01'],
            '税费为负但总和为正' => ['10.00', '-5.00'],
        ];
    }

    #[DataProvider('nonPositiveAmountProvider')]
    public function testIsPositiveWithNonPositiveAmountShouldReturnFalse(?string $money, ?string $tax): void
    {
        // Arrange
        $price = $this->createOrderPriceMock();
        $price->method('getMoney')->willReturn($money);
        $price->method('getTax')->willReturn($tax);

        // Act
        $result = PriceFilter::isPositive($price);

        // Assert
        $this->assertFalse($result);
    }

    /** @return array<string, array{?string, ?string}> */
    public static function nonPositiveAmountProvider(): array
    {
        return [
            '金额税费都为零' => ['0', '0'],
            '金额税费都为null' => [null, null],
            '金额为负税费也不足抵消' => ['-10.00', '5.00'],
            '金额税费都为负' => ['-10.00', '-5.00'],
            '金额为负数税费为零' => ['-10.00', '0'],
            '金额为零税费为负数' => ['0', '-5.00'],
            '负数抵消正数' => ['10.00', '-15.00'], // -5.00 总和
        ];
    }

    public function testIsPositiveWithStringNumbersShouldHandleCorrectly(): void
    {
        // Arrange - 测试字符串数字转换
        $price = $this->createOrderPriceMock();
        $price->method('getMoney')->willReturn('123.45');
        $price->method('getTax')->willReturn('67.89');

        // Act
        $result = PriceFilter::isPositive($price);

        // Assert
        $this->assertTrue($result);
    }

    public function testFilterCombinationScenarioShouldWorkCorrectly(): void
    {
        // Arrange - 综合场景测试
        $product = $this->createMock(OrderProduct::class);

        $price = $this->createOrderPriceMock();
        $price->method('getName')->willReturn('运费');
        $price->method('getType')->willReturn(PriceType::FREIGHT);
        $price->method('getProduct')->willReturn($product);
        $price->method('isPaid')->willReturn(true);
        $price->method('getMoney')->willReturn('15.00');
        $price->method('getTax')->willReturn('1.50');

        // Act & Assert - 多条件组合检验
        $this->assertTrue(PriceFilter::isFreightPrice($price));
        $this->assertTrue(PriceFilter::hasProduct($price));
        $this->assertTrue(PriceFilter::isPaid($price));
        $this->assertTrue(PriceFilter::isPositive($price));
    }

    /**
     * 创建OrderPrice Mock对象
     */
    private function createOrderPriceMock(): MockObject&OrderPrice
    {
        return $this->createMock(OrderPrice::class);
    }
}
