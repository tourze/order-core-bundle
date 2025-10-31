<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\DTO;

use Generator;
use OrderCoreBundle\DTO\DeliveryStockDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DeliveryStockDTO::class)]
final class DeliveryStockDTOTest extends TestCase
{
    public function testConstructorWithRequiredParameters(): void
    {
        $dto = new DeliveryStockDTO(1, 'SKU001', 10);

        $this->assertSame(1, $dto->getId());
        $this->assertSame('SKU001', $dto->getSkuCode());
        $this->assertSame(10, $dto->getQuantity());
        $this->assertSame('pending', $dto->getStatus());
        $this->assertFalse($dto->isReceived());
    }

    public function testConstructorWithAllParameters(): void
    {
        $dto = new DeliveryStockDTO(2, 'SKU002', 5, 'received');

        $this->assertSame(2, $dto->getId());
        $this->assertSame('SKU002', $dto->getSkuCode());
        $this->assertSame(5, $dto->getQuantity());
        $this->assertSame('received', $dto->getStatus());
        $this->assertTrue($dto->isReceived());
    }

    public function testGetId(): void
    {
        $dto = new DeliveryStockDTO(42, 'TEST_SKU', 1);

        $this->assertSame(42, $dto->getId());
    }

    public function testGetSkuCode(): void
    {
        $dto = new DeliveryStockDTO(1, 'PRODUCT_XYZ_123', 1);

        $this->assertSame('PRODUCT_XYZ_123', $dto->getSkuCode());
    }

    public function testGetQuantity(): void
    {
        $dto = new DeliveryStockDTO(1, 'SKU001', 99);

        $this->assertSame(99, $dto->getQuantity());
    }

    public function testGetStatus(): void
    {
        $dto1 = new DeliveryStockDTO(1, 'SKU001', 10);
        $this->assertSame('pending', $dto1->getStatus());

        $dto2 = new DeliveryStockDTO(2, 'SKU002', 5, 'shipped');
        $this->assertSame('shipped', $dto2->getStatus());
    }

    /**
     * @param array{id: int, skuCode: string, quantity: int, status: string, expectedIsReceived: bool} $testData
     */
    #[DataProvider('isReceivedDataProvider')]
    public function testIsReceived(array $testData): void
    {
        $dto = new DeliveryStockDTO(
            $testData['id'],
            $testData['skuCode'],
            $testData['quantity'],
            $testData['status']
        );

        $this->assertSame($testData['expectedIsReceived'], $dto->isReceived());
    }

    /**
     * @return \Generator<string, array{array{id: int, skuCode: string, quantity: int, status: string, expectedIsReceived: bool}}>
     */
    public static function isReceivedDataProvider(): \Generator
    {
        yield 'received status returns true' => [
            [
                'id' => 1,
                'skuCode' => 'SKU001',
                'quantity' => 10,
                'status' => 'received',
                'expectedIsReceived' => true,
            ],
        ];

        yield 'pending status returns false' => [
            [
                'id' => 2,
                'skuCode' => 'SKU002',
                'quantity' => 5,
                'status' => 'pending',
                'expectedIsReceived' => false,
            ],
        ];

        yield 'shipped status returns false' => [
            [
                'id' => 3,
                'skuCode' => 'SKU003',
                'quantity' => 15,
                'status' => 'shipped',
                'expectedIsReceived' => false,
            ],
        ];

        yield 'cancelled status returns false' => [
            [
                'id' => 4,
                'skuCode' => 'SKU004',
                'quantity' => 3,
                'status' => 'cancelled',
                'expectedIsReceived' => false,
            ],
        ];

        yield 'empty status returns false' => [
            [
                'id' => 5,
                'skuCode' => 'SKU005',
                'quantity' => 1,
                'status' => '',
                'expectedIsReceived' => false,
            ],
        ];

        yield 'case sensitive - Received with capital R returns false' => [
            [
                'id' => 6,
                'skuCode' => 'SKU006',
                'quantity' => 2,
                'status' => 'Received',
                'expectedIsReceived' => false,
            ],
        ];

        yield 'case sensitive - RECEIVED uppercase returns false' => [
            [
                'id' => 7,
                'skuCode' => 'SKU007',
                'quantity' => 8,
                'status' => 'RECEIVED',
                'expectedIsReceived' => false,
            ],
        ];
    }

    public function testDefaultStatusIsPending(): void
    {
        $dto = new DeliveryStockDTO(1, 'SKU001', 10);

        $this->assertSame('pending', $dto->getStatus());
        $this->assertFalse($dto->isReceived());
    }

    public function testReadOnlyProperties(): void
    {
        $dto = new DeliveryStockDTO(123, 'READONLY_TEST', 25, 'processing');

        // 验证所有属性都是只读的
        $this->assertSame(123, $dto->getId());
        $this->assertSame('READONLY_TEST', $dto->getSkuCode());
        $this->assertSame(25, $dto->getQuantity());
        $this->assertSame('processing', $dto->getStatus());
    }

    public function testEdgeCaseValues(): void
    {
        // 测试边界值
        $dto1 = new DeliveryStockDTO(0, '', 0, '');
        $this->assertSame(0, $dto1->getId());
        $this->assertSame('', $dto1->getSkuCode());
        $this->assertSame(0, $dto1->getQuantity());
        $this->assertSame('', $dto1->getStatus());
        $this->assertFalse($dto1->isReceived());

        // 测试负值
        $dto2 = new DeliveryStockDTO(-1, 'NEGATIVE_TEST', -10, 'negative_status');
        $this->assertSame(-1, $dto2->getId());
        $this->assertSame('NEGATIVE_TEST', $dto2->getSkuCode());
        $this->assertSame(-10, $dto2->getQuantity());
        $this->assertSame('negative_status', $dto2->getStatus());
        $this->assertFalse($dto2->isReceived());
    }

    public function testSpecialCharactersInSkuCode(): void
    {
        $specialSkuCodes = [
            'SKU-001_ABC',
            'SKU.001.XYZ',
            'SKU@001#ABC',
            'SKU 001 WITH SPACES',
            '中文SKU编码',
            'émoji-sku-🚀',
        ];

        foreach ($specialSkuCodes as $skuCode) {
            $dto = new DeliveryStockDTO(1, $skuCode, 10);
            $this->assertSame($skuCode, $dto->getSkuCode());
        }
    }

    public function testLargeValues(): void
    {
        $dto = new DeliveryStockDTO(PHP_INT_MAX, 'LARGE_TEST', PHP_INT_MAX, 'received');

        $this->assertSame(PHP_INT_MAX, $dto->getId());
        $this->assertSame('LARGE_TEST', $dto->getSkuCode());
        $this->assertSame(PHP_INT_MAX, $dto->getQuantity());
        $this->assertSame('received', $dto->getStatus());
        $this->assertTrue($dto->isReceived());
    }

    public function testMultipleInstancesAreIndependent(): void
    {
        $dto1 = new DeliveryStockDTO(1, 'SKU001', 10, 'pending');
        $dto2 = new DeliveryStockDTO(2, 'SKU002', 20, 'received');

        // 验证两个实例互不影响
        $this->assertSame(1, $dto1->getId());
        $this->assertSame(2, $dto2->getId());
        $this->assertSame('SKU001', $dto1->getSkuCode());
        $this->assertSame('SKU002', $dto2->getSkuCode());
        $this->assertSame(10, $dto1->getQuantity());
        $this->assertSame(20, $dto2->getQuantity());
        $this->assertFalse($dto1->isReceived());
        $this->assertTrue($dto2->isReceived());
    }

    public function testIsReceivedMethodExactMatch(): void
    {
        // 测试 isReceived 方法严格匹配 'received' 字符串
        $exactMatch = new DeliveryStockDTO(1, 'SKU001', 10, 'received');
        $this->assertTrue($exactMatch->isReceived());

        // 测试其他类似但不完全匹配的状态
        $almostMatch = new DeliveryStockDTO(2, 'SKU002', 10, ' received ');
        $this->assertFalse($almostMatch->isReceived());

        $partialMatch = new DeliveryStockDTO(3, 'SKU003', 10, 'received_confirmed');
        $this->assertFalse($partialMatch->isReceived());

        $substring = new DeliveryStockDTO(4, 'SKU004', 10, 'not_received');
        $this->assertFalse($substring->isReceived());
    }
}
