<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\ReceiveUserOrderParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(ReceiveUserOrderParam::class)]
final class ReceiveUserOrderParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new ReceiveUserOrderParam(contractId: '123');
        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithContractId(): void
    {
        $param = new ReceiveUserOrderParam(contractId: 'order-123');

        $this->assertSame('order-123', $param->contractId);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new ReceiveUserOrderParam(contractId: 'order-123');

        $violations = $validator->validate($param);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWithEmptyContractId(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new ReceiveUserOrderParam(contractId: '');

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(ReceiveUserOrderParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
