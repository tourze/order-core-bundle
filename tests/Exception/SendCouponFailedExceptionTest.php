<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\SendCouponFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(SendCouponFailedException::class)]
final class SendCouponFailedExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new SendCouponFailedException('Failed to send coupon');

        $this->assertSame('Failed to send coupon', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(SendCouponFailedException::class);
        $this->expectExceptionMessage('Test coupon send error');

        throw new SendCouponFailedException('Test coupon send error');
    }

    public function testExceptionExtendsException(): void
    {
        $exception = new SendCouponFailedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
