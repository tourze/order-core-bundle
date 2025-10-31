<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\EventSubscriber;

use OrderCoreBundle\EventSubscriber\TrackLogSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(TrackLogSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class TrackLogSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 该测试类不需要额外的设置
    }

    public function testCanBeInstantiated(): void
    {
        $subscriber = self::getService(TrackLogSubscriber::class);
        $this->assertInstanceOf(TrackLogSubscriber::class, $subscriber);
    }

    public function testHasEventListenerMethods(): void
    {
        $subscriber = self::getService(TrackLogSubscriber::class);

        // 验证事件监听方法存在
        $this->assertTrue(method_exists($subscriber, 'afterOrderReceived'));
    }

    public function testServiceIsRegistered(): void
    {
        $subscriber = self::getService(TrackLogSubscriber::class);
        $this->assertInstanceOf(TrackLogSubscriber::class, $subscriber);

        // 验证服务能够正常获取
        $this->assertNotNull($subscriber);
    }

    public function testAfterOrderReceived(): void
    {
        $subscriber = self::getService(TrackLogSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'afterOrderReceived'));
    }
}
