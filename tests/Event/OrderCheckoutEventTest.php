<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\OrderCheckoutEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(OrderCheckoutEvent::class)]
final class OrderCheckoutEventTest extends AbstractEventTestCase
{
    public function testCanBeInstantiated(): void
    {
        $event = new OrderCheckoutEvent();
        $this->assertInstanceOf(OrderCheckoutEvent::class, $event);
    }

    public function testGetResultReturnsEmptyArrayByDefault(): void
    {
        $event = new OrderCheckoutEvent();
        $this->assertSame([], $event->getResult());
    }

    public function testSetAndGetResult(): void
    {
        $event = new OrderCheckoutEvent();
        $result = ['orderId' => 123, 'total' => 99.99];

        $event->setResult($result);
        $this->assertSame($result, $event->getResult());
    }

    public function testContractAwareFunctionality(): void
    {
        $contract = $this->createMock(Contract::class);
        $event = new OrderCheckoutEvent();

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }
}
