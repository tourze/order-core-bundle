<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Command;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use OrderCoreBundle\Command\CancelExpiredOrdersCommand;
use OrderCoreBundle\Repository\ContractRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CancelExpiredOrdersCommand::class)]
#[RunTestsInSeparateProcesses]
final class CancelExpiredOrdersCommandTest extends AbstractCommandTestCase
{
    private CancelExpiredOrdersCommand $command;

    protected function getCommandTester(): CommandTester
    {
        $command = self::getContainer()->get(CancelExpiredOrdersCommand::class);
        self::assertInstanceOf(CancelExpiredOrdersCommand::class, $command);

        return new CommandTester($command);
    }

    protected function onSetUp(): void
    {
        /** @var CancelExpiredOrdersCommand $command */
        $command = self::getContainer()->get(CancelExpiredOrdersCommand::class);
        $this->command = $command;
    }

    #[Test]
    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('order:cancel-expired', $this->command->getName());
    }

    #[Test]
    public function testCommandHasCorrectDescription(): void
    {
        $this->assertEquals('Cancel expired unpaid orders and release stock automatically', $this->command->getDescription());
    }

    #[Test]
    public function testCommandCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CancelExpiredOrdersCommand::class, $this->command);
        $this->assertInstanceOf(Command::class, $this->command);
    }

    #[Test]
    public function testExecuteWithNoExpiredOrdersReturnsSuccess(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        // 不验证具体输出内容，因为实际数据库中可能有数据
    }

    #[Test]
    public function testExecuteInDryRunMode(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--dry-run' => true]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Running in dry-run mode', $output);
    }

    #[Test]
    public function testExecuteWithBatchSizeOption(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--batch-size' => '50']);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    #[Test]
    public function testExecuteWithLimitOption(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--limit' => '10']);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    #[Test]
    public function testOptionDryRun(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--dry-run' => true]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertTrue(
            str_contains($output, 'Dry-run completed. Would have cancelled') || str_contains($output, 'No expired orders found'),
            'Should either complete dry-run or find no orders. Actual output: ' . $output
        );
    }

    #[Test]
    public function testOptionBatchSize(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--batch-size' => 10]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    #[Test]
    public function testOptionLimit(): void
    {
        // 这是一个集成测试，使用真实的服务
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute(['--limit' => 5]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
