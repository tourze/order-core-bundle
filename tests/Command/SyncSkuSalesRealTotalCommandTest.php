<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Command;

use OrderCoreBundle\Command\SyncSkuSalesRealTotalCommand;
use OrderCoreBundle\Repository\ContractRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\ProductCoreBundle\Service\SkuService;

/**
 * @internal
 */
#[CoversClass(SyncSkuSalesRealTotalCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncSkuSalesRealTotalCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        $contractRepository = $this->createMock(ContractRepository::class);

        // Register only custom service mocks in container
        $container = self::getContainer();
        $container->set(ContractRepository::class, $contractRepository);
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncSkuSalesRealTotalCommand::class);
        $this->assertInstanceOf(SyncSkuSalesRealTotalCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getService(SyncSkuSalesRealTotalCommand::class);
        $this->assertNotNull($command);
        $this->assertEquals(SyncSkuSalesRealTotalCommand::NAME, $command->getName());
    }

    public function testCommandHasCorrectDescription(): void
    {
        $command = self::getService(SyncSkuSalesRealTotalCommand::class);
        $this->assertEquals('同步sku真实销量', $command->getDescription());
    }

    public function testCommandExecutionReturnsSuccess(): void
    {
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('任务结束', $commandTester->getDisplay());
    }
}
