<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\GetPayInformationFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(GetPayInformationFailedException::class)]
final class GetPayInformationFailedExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new GetPayInformationFailedException('Failed to get payment information');

        $this->assertSame('Failed to get payment information', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(GetPayInformationFailedException::class);
        $this->expectExceptionMessage('Test payment info error');

        throw new GetPayInformationFailedException('Test payment info error');
    }

    public function testExceptionExtendsException(): void
    {
        $exception = new GetPayInformationFailedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
