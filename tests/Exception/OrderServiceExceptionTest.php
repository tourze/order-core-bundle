<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\OrderServiceException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderServiceException::class)]
final class OrderServiceExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new OrderServiceException('Order service error');

        $this->assertSame('Order service error', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(OrderServiceException::class);
        $this->expectExceptionMessage('Test order service exception');

        throw new OrderServiceException('Test order service exception');
    }
}
