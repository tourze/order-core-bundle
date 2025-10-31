<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\UserNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(UserNotFoundException::class)]
final class UserNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new UserNotFoundException('User not found');

        $this->assertSame('User not found', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Test user not found');
        $this->expectExceptionCode(0);

        throw new UserNotFoundException('Test user not found');
    }
}
