<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\BadRequestException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(BadRequestException::class)]
final class BadRequestExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new BadRequestException();
        $this->assertInstanceOf(BadRequestException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test bad request message';
        $exception = new BadRequestException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Test bad request message';
        $code = 400;
        $exception = new BadRequestException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new BadRequestException('Bad request', 400, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
