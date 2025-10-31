<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\TestControllerException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(TestControllerException::class)]
final class TestControllerExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new TestControllerException('Test controller error');

        $this->assertSame('Test controller error', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(TestControllerException::class);
        $this->expectExceptionMessage('Test controller exception');

        throw new TestControllerException('Test controller exception');
    }
}
