<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\EventSubscriber;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\EventSubscriber\ContractListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractListener::class)]
#[RunTestsInSeparateProcesses]
final class ContractListenerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 该测试类不需要额外的设置
    }

    public function testCanBeInstantiated(): void
    {
        $listener = self::getService(ContractListener::class);
        $this->assertInstanceOf(ContractListener::class, $listener);
    }

    public function testPrePersist(): void
    {
        $listener = self::getService(ContractListener::class);
        $contract = new Contract();

        // EventSubscriber测试应该专注于业务逻辑
        // 在没有认证用户的情况下，contract应该保持null
        $this->assertNull($contract->getUser());

        $listener->prePersist($contract);

        // 没有认证用户时应该保持null
        $this->assertNull($contract->getUser());
    }
}
