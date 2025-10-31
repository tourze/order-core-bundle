<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\EventSubscriber;

use OrderCoreBundle\EventSubscriber\StockSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(StockSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class StockSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 该测试类不需要额外的设置
    }

    public function testCanBeInstantiated(): void
    {
        $subscriber = self::getService(StockSubscriber::class);
        $this->assertInstanceOf(StockSubscriber::class, $subscriber);
    }

    public function testHasEventListenerMethods(): void
    {
        $subscriber = self::getService(StockSubscriber::class);

        // 验证核心事件监听方法存在
        $this->assertTrue(method_exists($subscriber, 'onBeforeOrderCreated'));
        $this->assertTrue(method_exists($subscriber, 'onAfterOrderCreated'));
        $this->assertTrue(method_exists($subscriber, 'onOrderPaid'));
        $this->assertTrue(method_exists($subscriber, 'releaseLockedStock'));
    }

    public function testOnBeforeOrderCreatedExists(): void
    {
        $subscriber = self::getService(StockSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onBeforeOrderCreated'));
    }

    public function testOnAfterOrderCreatedExists(): void
    {
        $subscriber = self::getService(StockSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onAfterOrderCreated'));
    }

    public function testOnOrderPaidExists(): void
    {
        $subscriber = self::getService(StockSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onOrderPaid'));
    }

    public function testReleaseLockedStockExists(): void
    {
        $subscriber = self::getService(StockSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'releaseLockedStock'));
    }
}
