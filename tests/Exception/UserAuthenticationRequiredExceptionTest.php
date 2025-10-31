<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\UserAuthenticationRequiredException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(UserAuthenticationRequiredException::class)]
final class UserAuthenticationRequiredExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testIsRuntimeException(): void
    {
        $exception = new UserAuthenticationRequiredException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testCanBeCreatedWithMessage(): void
    {
        $message = 'User authentication required';
        $exception = new UserAuthenticationRequiredException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testCanBeCreatedWithMessageAndCode(): void
    {
        $message = 'Authentication required to access this resource';
        $code = 401;
        $exception = new UserAuthenticationRequiredException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testCanBeCreatedWithPreviousException(): void
    {
        $previous = new \Exception('Token expired');
        $exception = new UserAuthenticationRequiredException('User authentication required', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
