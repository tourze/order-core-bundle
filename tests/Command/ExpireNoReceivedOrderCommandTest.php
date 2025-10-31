<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Command;

use OrderCoreBundle\Command\ExpireNoReceivedOrderCommand;
use OrderCoreBundle\Repository\ContractRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(ExpireNoReceivedOrderCommand::class)]
#[RunTestsInSeparateProcesses]
final class ExpireNoReceivedOrderCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        $contractRepository = $this->createMock(ContractRepository::class);

        // Mock repository to return empty results
        $contractRepository->method('findBy')->willReturn([]);

        // Register only custom service mocks in container
        $container = self::getContainer();
        $container->set(ContractRepository::class, $contractRepository);
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(ExpireNoReceivedOrderCommand::class);
        $this->assertInstanceOf(ExpireNoReceivedOrderCommand::class, $command);

        return new CommandTester($command);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getService(ExpireNoReceivedOrderCommand::class);
        $this->assertNotNull($command);
        $this->assertEquals(ExpireNoReceivedOrderCommand::NAME, $command->getName());
    }

    public function testCommandHasCorrectDescription(): void
    {
        $command = self::getService(ExpireNoReceivedOrderCommand::class);
        $this->assertEquals('将发货但有结束收货时间的订单拉出来处理', $command->getDescription());
    }

    public function testCommandExecutionReturnsSuccess(): void
    {
        $commandTester = $this->getCommandTester();

        $exitCode = $commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }
}
