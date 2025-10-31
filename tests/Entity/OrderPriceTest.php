<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\OrderPrice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * @internal
 */
#[CoversClass(OrderPrice::class)]
final class OrderPriceTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OrderPrice();
    }

    public function testCanBeInstantiated(): void
    {
        $entity = new OrderPrice();
        $this->assertInstanceOf(OrderPrice::class, $entity);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    /** @return \Generator<string, array{string, mixed}> */
    public static function propertiesProvider(): \Generator
    {
        yield 'name' => ['name', 'Product Price'];
        yield 'currency' => ['currency', 'USD'];
        yield 'money' => ['money', '99.99'];
        yield 'tax' => ['tax', '10.00'];
        yield 'remark' => ['remark', 'Test remark'];
        yield 'paid' => ['paid', false];
        yield 'canRefund' => ['canRefund', true];
        yield 'refund' => ['refund', false];
        yield 'type' => ['type', PriceType::SALE];
        yield 'unitPrice' => ['unitPrice', '19.99'];
    }
}
