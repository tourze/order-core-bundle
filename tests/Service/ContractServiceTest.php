<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\ContractService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractService::class)]
#[RunTestsInSeparateProcesses]
final class ContractServiceTest extends AbstractIntegrationTestCase
{
    private ContractService $contractService;

    protected function onSetUp(): void
    {
        // 不需要调用 parent::onSetUp()，因为 AbstractIntegrationTestCase 的 onSetUp() 是抽象方法

        $this->contractService = self::getService(ContractService::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(ContractService::class, $this->contractService);
    }
}
