<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\UnsupportedResourceTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(UnsupportedResourceTypeException::class)]
final class UnsupportedResourceTypeExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new UnsupportedResourceTypeException('Unsupported resource type');

        $this->assertSame('Unsupported resource type', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(UnsupportedResourceTypeException::class);
        $this->expectExceptionMessage('Test unsupported resource');

        throw new UnsupportedResourceTypeException('Test unsupported resource');
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new UnsupportedResourceTypeException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
