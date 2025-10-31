<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Service\ProductPriceValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;

/**
 * @internal
 */
#[CoversClass(ProductPriceValidator::class)]
#[RunTestsInSeparateProcesses]
final class ProductPriceValidatorTest extends AbstractIntegrationTestCase
{
    private ProductPriceValidator $productPriceValidator;

    public function testIsValidForCurrencyWithNullProductShouldReturnTrueWhenPositiveTotal(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.00');
        $orderPrice->setTax('10.00');
        $orderPrice->setProduct(null);

        $products = new ArrayCollection();

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithNullProductShouldReturnFalseWhenNegativeTotal(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('-50.00');
        $orderPrice->setTax('-10.00');
        $orderPrice->setProduct(null);

        $products = new ArrayCollection();

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidForCurrencyWithProductHavingNullIdShouldReturnTrueWhenPositiveTotal(): void
    {
        // Arrange
        $orderProduct = new OrderProduct();
        $orderProduct->setValid(true);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('75.00');
        $orderPrice->setTax('5.00');
        $orderPrice->setProduct($orderProduct);

        $products = new ArrayCollection();

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithValidProductShouldReturnTrue(): void
    {
        // Arrange
        $orderProduct = $this->createOrderProduct(1, true);
        $products = new ArrayCollection([$orderProduct]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.00');
        $orderPrice->setTax('15.00');
        $orderPrice->setProduct($orderProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithInvalidProductShouldReturnFalse(): void
    {
        // Arrange
        $orderProduct = $this->createOrderProduct(1, false);
        $products = new ArrayCollection([$orderProduct]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('100.00');
        $orderPrice->setTax('15.00');
        $orderPrice->setProduct($orderProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidForCurrencyWithProductNotInCollectionShouldReturnTrueWhenPositiveTotal(): void
    {
        // Arrange
        $orderProduct = $this->createOrderProduct(1, true);
        $otherProduct = $this->createOrderProduct(2, false);
        $products = new ArrayCollection([$otherProduct]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('50.00');
        $orderPrice->setTax('5.00');
        $orderPrice->setProduct($orderProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithZeroTotalShouldReturnTrue(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('0.00');
        $orderPrice->setTax('0.00');
        $orderPrice->setProduct(null);

        $products = new ArrayCollection();

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithNullMoneyAndTaxShouldReturnTrue(): void
    {
        // Arrange
        $orderPrice = new OrderPrice();
        $orderPrice->setMoney(null);
        $orderPrice->setTax(null);
        $orderPrice->setProduct(null);

        $products = new ArrayCollection();

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithMultipleProductsAndValidTargetShouldReturnTrue(): void
    {
        // Arrange
        $targetProduct = $this->createOrderProduct(5, true);
        $otherProduct1 = $this->createOrderProduct(3, false);
        $otherProduct2 = $this->createOrderProduct(7, true);

        $products = new ArrayCollection([$otherProduct1, $targetProduct, $otherProduct2]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('200.00');
        $orderPrice->setTax('20.00');
        $orderPrice->setProduct($targetProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    public function testIsValidForCurrencyWithPositiveTotalButInvalidProductShouldReturnFalse(): void
    {
        // Arrange
        $invalidProduct = $this->createOrderProduct(10, false);
        $products = new ArrayCollection([$invalidProduct]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('1000.00');
        $orderPrice->setTax('100.00');
        $orderPrice->setProduct($invalidProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidForCurrencyWithValidProductButNegativeTotalShouldReturnFalse(): void
    {
        // Arrange
        $validProduct = $this->createOrderProduct(15, true);
        $products = new ArrayCollection([$validProduct]);

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('-100.00');
        $orderPrice->setTax('-20.00');
        $orderPrice->setProduct($validProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertFalse($result);
    }

    public function testIsValidForCurrencyWithEmptyProductCollectionShouldDependOnlyOnTotal(): void
    {
        // Arrange
        $orderProduct = $this->createOrderProduct(99, true);
        $products = new ArrayCollection(); // Empty collection

        $orderPrice = new OrderPrice();
        $orderPrice->setMoney('50.00');
        $orderPrice->setTax('5.00');
        $orderPrice->setProduct($orderProduct);

        // Act
        $result = $this->productPriceValidator->isValidForCurrency($orderPrice, $products);

        // Assert
        $this->assertTrue($result);
    }

    private function createOrderProduct(int $id, bool $isValid): OrderProduct
    {
        $orderProduct = new OrderProduct();
        $orderProduct->setValid($isValid);

        // Use reflection to set the private ID field
        $reflection = new \ReflectionClass($orderProduct);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($orderProduct, $id);

        // Create basic SKU and SPU for more realistic test data
        $spu = new Spu();
        $spu->setTitle('Test Product');

        $sku = new Sku();
        $sku->setUnit('piece');
        $sku->setSpu($spu);

        $orderProduct->setSku($sku);
        $orderProduct->setSpu($spu);

        return $orderProduct;
    }

    protected function onSetUp(): void
    {
        $this->productPriceValidator = self::getService(ProductPriceValidator::class);
    }
}
