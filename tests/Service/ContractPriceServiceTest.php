<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Service\ContractPriceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractPriceService::class)]
#[RunTestsInSeparateProcesses]
final class ContractPriceServiceTest extends AbstractIntegrationTestCase
{
    private ContractPriceService $contractPriceService;

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(ContractPriceService::class, $this->contractPriceService);
    }

    public function testGetDisplayPriceShouldReturnEnvironmentValueOrDefault(): void
    {
        // Arrange
        $_ENV['DISPLAY_FREE_PRICE'] = '免费商品';
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getDisplayPrice($contract);

        // Assert
        $this->assertSame('免费商品', $result);

        // Cleanup
        unset($_ENV['DISPLAY_FREE_PRICE']);
    }

    public function testGetDisplayPriceWithoutEnvironmentShouldReturnDefault(): void
    {
        // Arrange
        unset($_ENV['DISPLAY_FREE_PRICE']);
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getDisplayPrice($contract);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testGetDisplayTaxPriceShouldReturnEnvironmentValueOrDefault(): void
    {
        // Arrange
        $_ENV['DISPLAY_FREE_PRICE'] = '税后免费';
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getDisplayTaxPrice($contract);

        // Assert
        $this->assertSame('税后免费', $result);

        // Cleanup
        unset($_ENV['DISPLAY_FREE_PRICE']);
    }

    public function testGetPurePriceWithEmptyContractShouldReturnFreeText(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getPurePrice($contract);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testGetPureFreightPriceWithEmptyContractShouldReturnFreeShipping(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getPureFreightPrice($contract);

        // Assert
        $this->assertSame('包邮', $result);
    }

    public function testGetCurrencyPricesWithEmptyContractShouldReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getCurrencyPrices($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testGetFreightPricesWithEmptyProductsShouldReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->getFreightPrices($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testGetPayPricesShouldAlwaysReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractPriceService->getPayPrices($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testCalcPriceByCurrencyWithEmptyContractShouldReturnZero(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractPriceService->calcPriceByCurrency($contract, 'CNY');

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testIsPayableWithEmptyPayPricesShouldReturnFalse(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractPriceService->isPayable($contract);

        // Assert
        $this->assertFalse($result);
    }

    public function testFormatPricesForDisplayWithEmptyCollectionShouldReturnEmptyArray(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->formatPricesForDisplay($prices);

        // Assert
        $this->assertSame([], $result);
    }

    public function testGetDisplayPriceFromCollectionWithEmptyCollectionShouldReturnFreeText(): void
    {
        // Arrange
        $prices = new ArrayCollection();
        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getDisplayPriceFromCollection($prices);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testGetDisplayTaxPriceFromCollectionWithEmptyCollectionShouldReturnFreeText(): void
    {
        // Arrange
        $prices = new ArrayCollection();
        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getDisplayTaxPriceFromCollection($prices);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testSumPriceByCurrencyWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->sumPriceByCurrency($prices, 'CNY');

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testSumTaxPriceByCurrencyWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->sumTaxPriceByCurrency($prices, 'CNY');

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testGetTotalTaxPriceWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->getTotalTaxPrice($prices);

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testGetTotalPriceWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->getTotalPrice($prices);

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testGetTotalTaxWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->getTotalTax($prices);

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testGetTaxRateWithEmptyCollectionShouldReturnZero(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->getTaxRate($prices);

        // Assert
        $this->assertSame(0.0, $result);
    }

    public function testGetDisplayUnitPriceWithEmptyCollectionShouldReturnFreeText(): void
    {
        // Arrange
        $prices = new ArrayCollection();
        $quantity = 5;
        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getDisplayUnitPrice($prices, $quantity);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testGetDisplayUnitTaxPriceWithEmptyCollectionShouldReturnFreeText(): void
    {
        // Arrange
        $prices = new ArrayCollection();
        $quantity = 5;
        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getDisplayUnitTaxPrice($prices, $quantity);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testGetDisplayUnitPriceWithZeroQuantityShouldReturnFreeText(): void
    {
        // Arrange
        $price = $this->createMock(OrderPrice::class);
        $price->method('getMoney')->willReturn('100.0');
        $price->method('getCurrency')->willReturn('CNY');

        /** @var ArrayCollection<int, OrderPrice> $prices */
        $prices = new ArrayCollection([$price]);
        $quantity = 0;
        unset($_ENV['DISPLAY_FREE_PRICE']);

        // Act
        $result = $this->contractPriceService->getDisplayUnitPrice($prices, $quantity);

        // Assert
        $this->assertSame('免费', $result);
    }

    public function testCollectNonFreightPricesFromCollectionWithEmptyCollectionShouldReturnEmptyArray(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->collectNonFreightPricesFromCollection($prices);

        // Assert
        $this->assertSame([], $result);
    }

    public function testCollectFreightPricesOnlyWithEmptyCollectionShouldReturnEmptyArray(): void
    {
        // Arrange
        $prices = new ArrayCollection();

        // Act
        $result = $this->contractPriceService->collectFreightPricesOnly($prices);

        // Assert
        $this->assertSame([], $result);
    }

    protected function onSetUp(): void
    {
        $this->contractPriceService = self::getService(ContractPriceService::class);
    }
}
