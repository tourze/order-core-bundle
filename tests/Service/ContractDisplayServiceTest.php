<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Service\ContractDisplayService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractDisplayService::class)]
#[RunTestsInSeparateProcesses]
final class ContractDisplayServiceTest extends AbstractIntegrationTestCase
{
    private ContractDisplayService $contractDisplayService;

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(ContractDisplayService::class, $this->contractDisplayService);
    }

    public function testGetStatusTextWithPayingStateShouldReturnPendingPayment(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getState')->willReturn(OrderState::PAYING);

        // Act
        $result = $this->contractDisplayService->getStatusText($contract);

        // Assert
        $this->assertSame('待付款', $result);
    }

    public function testGetStatusTextWithInitStateShouldReturnPendingPayment(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getState')->willReturn(OrderState::INIT);

        // Act
        $result = $this->contractDisplayService->getStatusText($contract);

        // Assert
        $this->assertSame('待付款', $result);
    }

    public function testGetStatusTextWithPaidStateShouldReturnPendingShipment(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getState')->willReturn(OrderState::PAID);

        // Act
        $result = $this->contractDisplayService->getStatusText($contract);

        // Assert
        $this->assertSame('待发货', $result);
    }

    public function testGetStatusTextWithShippedStateShouldReturnPendingDelivery(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getState')->willReturn(OrderState::SHIPPED);

        // Act
        $result = $this->contractDisplayService->getStatusText($contract);

        // Assert
        $this->assertSame('待收货', $result);
    }

    public function testGetStatusTextWithExpiredStateShouldReturnOrderException(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getState')->willReturn(OrderState::EXPIRED);

        // Act
        $result = $this->contractDisplayService->getStatusText($contract);

        // Assert
        $this->assertSame('订单异常', $result);
    }

    public function testGetDeliverStateWithNoProductsShouldReturnNull(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDisplayService->getDeliverState($contract);

        // Assert
        $this->assertNull($result);
    }

    public function testGetSupplierAuditStatusShouldAlwaysReturnFalse(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);

        // Act
        $result = $this->contractDisplayService->getSupplierAuditStatus($contract);

        // Assert
        $this->assertFalse($result);
    }

    public function testRenderContractsWithEmptyContactsShouldReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getContacts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDisplayService->renderContracts($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testRenderProductsWithEmptyProductsShouldReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDisplayService->renderProducts($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testToSelectItemShouldReturnCorrectFormat(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getSn')->willReturn('ORDER-20240101-001');
        $contract->method('getId')->willReturn(12345);

        // Act
        $result = $this->contractDisplayService->toSelectItem($contract);

        // Assert
        $expected = [
            'label' => 'ORDER-20240101-001',
            'text' => 'ORDER-20240101-001',
            'value' => 12345,
        ];
        $this->assertEquals($expected, $result);
    }

    public function testGetAppendPricesWithEmptyPricesShouldReturnEmptyArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getPrices')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDisplayService->getAppendPrices($contract);

        // Assert
        $this->assertSame([], $result);
    }

    public function testIsNeedConsigneeWithEmptyProductsShouldReturnFalse(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractDisplayService->isNeedConsignee($contract);

        // Assert
        $this->assertFalse($result);
    }

    public function testMapToCheckoutArrayWithEmptyItemsShouldReturnEmptyArray(): void
    {
        // Arrange
        $items = [];

        // Act
        $result = $this->contractDisplayService->mapToCheckoutArray($items);

        // Assert
        $this->assertSame([], $result);
    }

    protected function onSetUp(): void
    {
        $this->contractDisplayService = self::getService(ContractDisplayService::class);
    }
}
