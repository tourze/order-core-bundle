<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\GetOrderTrackLogsParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(GetOrderTrackLogsParam::class)]
final class GetOrderTrackLogsParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new GetOrderTrackLogsParam(orderId: '123');
        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithOrderId(): void
    {
        $param = new GetOrderTrackLogsParam(orderId: 'C202312001');

        $this->assertSame('C202312001', $param->orderId);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetOrderTrackLogsParam(orderId: 'order-123');

        $violations = $validator->validate($param);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWithEmptyOrderId(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new GetOrderTrackLogsParam(orderId: '');

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(GetOrderTrackLogsParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
