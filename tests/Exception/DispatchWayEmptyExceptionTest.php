<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\DispatchWayEmptyException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(DispatchWayEmptyException::class)]
final class DispatchWayEmptyExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new DispatchWayEmptyException('Dispatch way is empty');

        $this->assertSame('Dispatch way is empty', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(DispatchWayEmptyException::class);
        $this->expectExceptionMessage('Test dispatch way empty error');

        throw new DispatchWayEmptyException('Test dispatch way empty error');
    }

    public function testExceptionExtendsException(): void
    {
        $exception = new DispatchWayEmptyException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
