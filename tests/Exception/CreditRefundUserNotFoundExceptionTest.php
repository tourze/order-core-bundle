<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\CreditRefundUserNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CreditRefundUserNotFoundException::class)]
final class CreditRefundUserNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionWithDefaultMessage(): void
    {
        $exception = new CreditRefundUserNotFoundException();

        $this->assertSame('积分退款时用户不存在', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithCustomMessage(): void
    {
        $exception = new CreditRefundUserNotFoundException('自定义消息', 123);

        $this->assertSame('自定义消息', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('原始异常');
        $exception = new CreditRefundUserNotFoundException('积分退款失败', 456, $previous);

        $this->assertSame('积分退款失败', $exception->getMessage());
        $this->assertSame(456, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
