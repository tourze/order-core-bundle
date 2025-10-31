<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\EventSubscriber;

use OrderCoreBundle\EventSubscriber\PaymentStatusSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentStatusSubscriber::class)]
#[RunTestsInSeparateProcesses]
class PaymentStatusSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化
    }

    public function testCanBeInstantiated(): void
    {
        $subscriber = self::getService(PaymentStatusSubscriber::class);
        $this->assertInstanceOf(PaymentStatusSubscriber::class, $subscriber);
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $subscriber = self::getService(PaymentStatusSubscriber::class);
        $this->assertInstanceOf(PaymentStatusSubscriber::class, $subscriber);
    }

    public function testHasEventListenerMethods(): void
    {
        $subscriber = self::getService(PaymentStatusSubscriber::class);

        $this->assertTrue(method_exists($subscriber, 'onPaymentSuccess'));
        $this->assertTrue(method_exists($subscriber, 'onPaymentFailed'));
    }

    public function testOnPaymentSuccessMethodExists(): void
    {
        $subscriber = self::getService(PaymentStatusSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onPaymentSuccess'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(PaymentStatusSubscriber::class, 'onPaymentSuccess');
        $this->assertTrue($reflection->isPublic());
    }

    public function testOnPaymentFailedMethodExists(): void
    {
        $subscriber = self::getService(PaymentStatusSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onPaymentFailed'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(PaymentStatusSubscriber::class, 'onPaymentFailed');
        $this->assertTrue($reflection->isPublic());
    }
}
