<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\CancelUserOrderParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(CancelUserOrderParam::class)]
final class CancelUserOrderParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new CancelUserOrderParam(contractId: '123');
        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithAllParameters(): void
    {
        $param = new CancelUserOrderParam(
            contractId: 'order-123',
            cancelReason: '不想要了'
        );

        $this->assertSame('order-123', $param->contractId);
        $this->assertSame('不想要了', $param->cancelReason);
    }

    public function testConstructorWithRequiredParametersOnly(): void
    {
        $param = new CancelUserOrderParam(contractId: 'order-456');

        $this->assertSame('order-456', $param->contractId);
        $this->assertNull($param->cancelReason);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new CancelUserOrderParam(
            contractId: 'order-123',
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

        $param = new CancelUserOrderParam(
            contractId: '',
            cancelReason: null
        );

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(CancelUserOrderParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
