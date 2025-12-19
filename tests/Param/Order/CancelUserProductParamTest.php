<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\CancelUserProductParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(CancelUserProductParam::class)]
final class CancelUserProductParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new CancelUserProductParam(contractId: '123', productId: '456');
        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithAllParameters(): void
    {
        $param = new CancelUserProductParam(
            contractId: 'order-123',
            productId: 'product-456',
            cancelReason: '商品有问题'
        );

        $this->assertSame('order-123', $param->contractId);
        $this->assertSame('product-456', $param->productId);
        $this->assertSame('商品有问题', $param->cancelReason);
    }

    public function testConstructorWithRequiredParametersOnly(): void
    {
        $param = new CancelUserProductParam(
            contractId: 'order-456',
            productId: 'product-789'
        );

        $this->assertSame('order-456', $param->contractId);
        $this->assertSame('product-789', $param->productId);
        $this->assertNull($param->cancelReason);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new CancelUserProductParam(
            contractId: 'order-123',
            productId: 'product-456',
            cancelReason: '取消原因'
        );

        $violations = $validator->validate($param);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWithEmptyContractId(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new CancelUserProductParam(
            contractId: '',
            productId: 'product-456'
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidationFailsWithEmptyProductId(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new CancelUserProductParam(
            contractId: 'order-123',
            productId: ''
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(CancelUserProductParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
