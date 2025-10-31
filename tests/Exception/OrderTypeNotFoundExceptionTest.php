<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\OrderTypeNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderTypeNotFoundException::class)]
final class OrderTypeNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new OrderTypeNotFoundException('Order type not found');

        $this->assertSame('Order type not found', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(OrderTypeNotFoundException::class);
        $this->expectExceptionMessage('Test order type not found');

        throw new OrderTypeNotFoundException('Test order type not found');
    }

    public function testExceptionExtendsException(): void
    {
        $exception = new OrderTypeNotFoundException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
