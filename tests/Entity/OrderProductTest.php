<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\OrderProduct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(OrderProduct::class)]
final class OrderProductTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OrderProduct();
    }

    public function testCanBeInstantiated(): void
    {
        $entity = new OrderProduct();
        $this->assertInstanceOf(OrderProduct::class, $entity);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    /** @return \Generator<string, array{string, mixed}> */
    public static function propertiesProvider(): \Generator
    {
        yield 'valid' => ['valid', true];
        yield 'currency' => ['currency', 'USD'];
        yield 'quantity' => ['quantity', 5];
        yield 'remark' => ['remark', 'Test remark'];
        yield 'source' => ['source', 'test_source'];
        yield 'skuUnit' => ['skuUnit', 'piece'];
        yield 'audited' => ['audited', true];
        yield 'skuDispatchPeriod' => ['skuDispatchPeriod', 7];
        yield 'spuTitle' => ['spuTitle', 'Test Product'];
    }
}
