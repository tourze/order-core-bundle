<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\OrderReceiveNotStartedException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderReceiveNotStartedException::class)]
final class OrderReceiveNotStartedExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new OrderReceiveNotStartedException('Order receive not started yet');

        $this->assertSame('Order receive not started yet', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(OrderReceiveNotStartedException::class);
        $this->expectExceptionMessage('Test order not started');

        throw new OrderReceiveNotStartedException('Test order not started');
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new OrderReceiveNotStartedException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
