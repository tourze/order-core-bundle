<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Service\OrderProductDisplayService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(OrderProductDisplayService::class)]
#[RunTestsInSeparateProcesses]
final class OrderProductDisplayServiceTest extends AbstractIntegrationTestCase
{
    private OrderProductDisplayService $orderProductDisplayService;

    protected function onSetUp(): void
    {
        $this->orderProductDisplayService = self::getService(OrderProductDisplayService::class);
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(OrderProductDisplayService::class, $this->orderProductDisplayService);
    }

    public function testGetOrderProductsGroupedReturnsCorrectStructure(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->getOrderProductsGrouped($contract);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('normal', $result);
        $this->assertArrayHasKey('gifts', $result);
        $this->assertArrayHasKey('redeems', $result);
    }

    public function testGetNormalPurchaseProductsReturnsArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->getNormalPurchaseProducts($contract);

        // Assert
        $this->assertIsArray($result);
    }

    public function testGetGiftProductsReturnsArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->getGiftProducts($contract);

        // Assert
        $this->assertIsArray($result);
    }

    public function testGetRedeemProductsReturnsArray(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->getRedeemProducts($contract);

        // Assert
        $this->assertIsArray($result);
    }

    public function testHasGiftsReturnsBool(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->hasGifts($contract);

        // Assert
        $this->assertIsBool($result);
    }

    public function testHasRedeemsReturnsBool(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->hasRedeems($contract);

        // Assert
        $this->assertIsBool($result);
    }

    public function testGetOrderProductStatsReturnsCorrectStructure(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);

        // Act
        $result = $this->orderProductDisplayService->getOrderProductStats($contract);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('normal', $result);
        $this->assertArrayHasKey('gifts', $result);
        $this->assertArrayHasKey('redeems', $result);
        $this->assertIsInt($result['total']);
        $this->assertIsInt($result['normal']);
        $this->assertIsInt($result['gifts']);
        $this->assertIsInt($result['redeems']);
    }
}
