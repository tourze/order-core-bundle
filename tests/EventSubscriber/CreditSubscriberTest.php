<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\EventSubscriber;

use OrderCoreBundle\EventSubscriber\CreditSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(CreditSubscriber::class)]
#[RunTestsInSeparateProcesses]
class CreditSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化
    }

    public function testCanBeInstantiated(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertInstanceOf(CreditSubscriber::class, $subscriber);
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertInstanceOf(CreditSubscriber::class, $subscriber);
    }

    public function testHasEventListenerMethods(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);

        $this->assertTrue(method_exists($subscriber, 'checkCreditEnough'));
        $this->assertTrue(method_exists($subscriber, 'onCreditPriceRefund'));
        $this->assertTrue(method_exists($subscriber, 'payBackCredit'));
        $this->assertTrue(method_exists($subscriber, 'payCreditPoint'));
    }

    public function testCheckCreditEnoughMethodExists(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'checkCreditEnough'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(CreditSubscriber::class, 'checkCreditEnough');
        $this->assertTrue($reflection->isPublic());
    }

    public function testOnCreditPriceRefundMethodExists(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'onCreditPriceRefund'));
    }

    public function testPayBackCreditMethodExists(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'payBackCredit'));
    }

    public function testPayCreditPointMethodExists(): void
    {
        $subscriber = self::getService(CreditSubscriber::class);
        $this->assertTrue(method_exists($subscriber, 'payCreditPoint'));
    }
}
