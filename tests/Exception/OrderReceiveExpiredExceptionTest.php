<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\OrderReceiveExpiredException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderReceiveExpiredException::class)]
final class OrderReceiveExpiredExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new OrderReceiveExpiredException('Order receive time has expired');

        $this->assertSame('Order receive time has expired', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(OrderReceiveExpiredException::class);
        $this->expectExceptionMessage('Test order receive expired');

        throw new OrderReceiveExpiredException('Test order receive expired');
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new OrderReceiveExpiredException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
