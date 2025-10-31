<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\OrderStateNotAllowedException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderStateNotAllowedException::class)]
final class OrderStateNotAllowedExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new OrderStateNotAllowedException('Order state not allowed for this operation');

        $this->assertSame('Order state not allowed for this operation', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(OrderStateNotAllowedException::class);
        $this->expectExceptionMessage('Test order state error');

        throw new OrderStateNotAllowedException('Test order state error');
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new OrderStateNotAllowedException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
