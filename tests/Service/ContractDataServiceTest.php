<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Service\ContractDataService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractDataService::class)]
#[RunTestsInSeparateProcesses]
final class ContractDataServiceTest extends AbstractIntegrationTestCase
{
    private ContractDataService $contractDataService;

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(ContractDataService::class, $this->contractDataService);
    }

    public function testGetContactPersonWithEmptyContractShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getContacts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getContactPerson($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetContactPhoneWithEmptyContractShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getContacts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getContactPhone($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetContactAddressWithEmptyContractShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getContacts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getContactAddress($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetSpuGtinWithEmptyProductsShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getSpuGtin($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetSpuIdWithEmptyProductsShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getSpuId($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testExportSkuIdWithEmptyProductsShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->exportSkuId($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetOpenIdWithoutUserShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getUser')->willReturn(null);

        // Act
        $result = $this->contractDataService->getOpenId($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetUserUnionIdShouldAlwaysReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getUser')->willReturn(null);

        // Act
        $result = $this->contractDataService->getUserUnionId($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetOrderUserIdWithoutUserShouldReturnEmptyString(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getUser')->willReturn(null);

        // Act
        $result = $this->contractDataService->getOrderUserId($contract);

        // Assert
        $this->assertSame('', $result);
    }

    public function testGetProductQuantityWithEmptyProductsShouldReturnZero(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getProductQuantity($contract);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testGetValidProductQuantityWithEmptyProductsShouldReturnZero(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDataService->getValidProductQuantity($contract);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testGetDeliverQuantityWithoutDeliveryServiceShouldReturnZero(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractDataService->getDeliverQuantity($contract);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testGetReceivedQuantityWithoutDeliveryServiceShouldReturnZero(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractDataService->getReceivedQuantity($contract);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testGetDeliverFirstTimeWithoutDeliveryServiceShouldReturnNull(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractDataService->getDeliverFirstTime($contract);

        // Assert
        $this->assertNull($result);
    }

    public function testGetDeliverLastTimeWithoutDeliveryServiceShouldReturnNull(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractDataService->getDeliverLastTime($contract);

        // Assert
        $this->assertNull($result);
    }

    protected function onSetUp(): void
    {
        $this->contractDataService = self::getService(ContractDataService::class);
    }
}
