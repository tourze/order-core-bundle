<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\GetUserOrderListParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPCPaginatorBundle\Param\PaginatorParamInterface;

/**
 * @internal
 */
#[CoversClass(GetUserOrderListParam::class)]
final class GetUserOrderListParamTest extends TestCase
{
    public function testImplementsPaginatorParamInterface(): void
    {
        $param = new GetUserOrderListParam();
        $this->assertInstanceOf(PaginatorParamInterface::class, $param);
    }

    public function testConstructorWithAllParameters(): void
    {
        $param = new GetUserOrderListParam(
            orderSn: 'C202312001',
            spuId: 'spu-123',
            skuId: 'sku-456',
            spuCategories: ['cat1', 'cat2'],
            spuTypes: ['type1'],
            orderStates: ['paid', 'shipped'],
            status: 'active',
            createTimeBegin: '2023-01-01',
            createTimeEnd: '2023-12-31',
            storeId: 'store-1',
            pageSize: 20,
            currentPage: 2,
            lastId: 100
        );

        $this->assertSame('C202312001', $param->orderSn);
        $this->assertSame('spu-123', $param->spuId);
        $this->assertSame('sku-456', $param->skuId);
        $this->assertSame(['cat1', 'cat2'], $param->spuCategories);
        $this->assertSame(['type1'], $param->spuTypes);
        $this->assertSame(['paid', 'shipped'], $param->orderStates);
        $this->assertSame('active', $param->status);
        $this->assertSame('2023-01-01', $param->createTimeBegin);
        $this->assertSame('2023-12-31', $param->createTimeEnd);
        $this->assertSame('store-1', $param->storeId);
        $this->assertSame(20, $param->pageSize);
        $this->assertSame(2, $param->currentPage);
        $this->assertSame(100, $param->lastId);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $param = new GetUserOrderListParam();

        $this->assertSame('', $param->orderSn);
        $this->assertSame('', $param->spuId);
        $this->assertSame('', $param->skuId);
        $this->assertSame([], $param->spuCategories);
        $this->assertSame([], $param->spuTypes);
        $this->assertSame([], $param->orderStates);
        $this->assertSame('all', $param->status);
        $this->assertSame('', $param->createTimeBegin);
        $this->assertSame('', $param->createTimeEnd);
        $this->assertSame('', $param->storeId);
        $this->assertSame(10, $param->pageSize);
        $this->assertSame(1, $param->currentPage);
        $this->assertNull($param->lastId);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetUserOrderListParam(
            pageSize: 50,
            currentPage: 5
        );

        $violations = $validator->validate($param);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWithInvalidPageSize(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetUserOrderListParam(
            pageSize: 0
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidationFailsWithExcessivePageSize(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetUserOrderListParam(
            pageSize: 3000
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidationFailsWithInvalidCurrentPage(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetUserOrderListParam(
            currentPage: 0
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(GetUserOrderListParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
