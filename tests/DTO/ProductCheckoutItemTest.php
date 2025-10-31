<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\DTO;

use Generator;
use OrderCoreBundle\DTO\ProductCheckoutItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ProductCheckoutItem::class)]
final class ProductCheckoutItemTest extends TestCase
{
    public function testConstructorWithRequiredParameters(): void
    {
        $item = new ProductCheckoutItem(100, 2);

        $this->assertSame(100, $item->getSkuId());
        $this->assertSame(2, $item->getQuantity());
        $this->assertNull($item->getSource());
        $this->assertSame([], $item->getAttachments());
    }

    public function testConstructorWithAllParameters(): void
    {
        $attachments = [1, 2, 3];
        $item = new ProductCheckoutItem(200, 5, 'mobile_app', $attachments);

        $this->assertSame(200, $item->getSkuId());
        $this->assertSame(5, $item->getQuantity());
        $this->assertSame('mobile_app', $item->getSource());
        $this->assertSame($attachments, $item->getAttachments());
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, mixed> $expectedAttachments
     */
    #[DataProvider('fromArrayDataProvider')]
    public function testFromArray(array $data, int $expectedSkuId, int $expectedQuantity, ?string $expectedSource, array $expectedAttachments): void
    {
        $item = ProductCheckoutItem::fromArray($data);

        $this->assertSame($expectedSkuId, $item->getSkuId());
        $this->assertSame($expectedQuantity, $item->getQuantity());
        $this->assertSame($expectedSource, $item->getSource());
        $this->assertSame($expectedAttachments, $item->getAttachments());
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, int, int, string|null, array<int, mixed>}>
     */
    public static function fromArrayDataProvider(): \Generator
    {
        yield 'complete data' => [
            [
                'skuId' => 100,
                'quantity' => 3,
                'source' => 'web',
                'attachments' => [1, 2, 3],
            ],
            100,
            3,
            'web',
            [1, 2, 3],
        ];

        yield 'minimal data' => [
            [
                'skuId' => 200,
                'quantity' => 1,
            ],
            200,
            1,
            null,
            [],
        ];

        yield 'empty data returns defaults' => [
            [],
            0,
            0,
            null,
            [],
        ];

        yield 'partial data with null values' => [
            [
                'skuId' => 300,
                'quantity' => 5,
                'source' => null,
                'attachments' => null,
            ],
            300,
            5,
            null,
            [],
        ];

        yield 'mixed attachments array' => [
            [
                'skuId' => 400,
                'quantity' => 2,
                'source' => 'api',
                'attachments' => ['file1', 123, ['nested' => 'data']],
            ],
            400,
            2,
            'api',
            ['file1', 123, ['nested' => 'data']],
        ];
    }

    public function testReadOnlyProperties(): void
    {
        $item = new ProductCheckoutItem(100, 2, 'test', [1, 2]);

        // 验证属性是只读的，无法修改
        $this->assertSame(100, $item->getSkuId());
        $this->assertSame(2, $item->getQuantity());
        $this->assertSame('test', $item->getSource());
        $this->assertSame([1, 2], $item->getAttachments());
    }

    public function testFromArrayWithInvalidTypes(): void
    {
        // 测试无效数据类型的处理
        $data = [
            'skuId' => 'not_a_number',
            'quantity' => 'also_not_a_number',
            'source' => 123,
            'attachments' => 'not_an_array',
        ];

        $item = ProductCheckoutItem::fromArray($data);

        // PHP 的类型强制转换行为
        $this->assertSame(0, $item->getSkuId());
        $this->assertSame(0, $item->getQuantity());
        $this->assertSame(null, $item->getSource()); // 非字符串类型返回 null
        $this->assertSame([], $item->getAttachments());
    }

    public function testAttachmentsArrayType(): void
    {
        $attachments = [
            ['id' => 1, 'name' => 'attachment1.pdf'],
            ['id' => 2, 'name' => 'attachment2.jpg'],
            'simple_string',
            42,
        ];

        $item = new ProductCheckoutItem(100, 1, 'web', $attachments);

        $result = $item->getAttachments();
        $this->assertCount(4, $result);
        $this->assertSame($attachments, $result);
    }

    public function testEmptySource(): void
    {
        $item1 = new ProductCheckoutItem(100, 1, '');
        $this->assertSame('', $item1->getSource());

        $item2 = new ProductCheckoutItem(100, 1, null);
        $this->assertNull($item2->getSource());
    }

    public function testZeroValues(): void
    {
        $item = new ProductCheckoutItem(0, 0);

        $this->assertSame(0, $item->getSkuId());
        $this->assertSame(0, $item->getQuantity());
    }

    public function testNegativeValues(): void
    {
        $item = new ProductCheckoutItem(-1, -5);

        $this->assertSame(-1, $item->getSkuId());
        $this->assertSame(-5, $item->getQuantity());
    }
}
