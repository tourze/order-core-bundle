<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use OrderCoreBundle\Exception\ContractNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ContractNotFoundException::class)]
final class ContractNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionIsCreatedWithMessage(): void
    {
        $exception = new ContractNotFoundException('Contract not found');

        $this->assertSame('Contract not found', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(ContractNotFoundException::class);
        $this->expectExceptionMessage('Test contract not found');
        $this->expectExceptionCode(404);

        throw new ContractNotFoundException('Test contract not found');
    }
}
