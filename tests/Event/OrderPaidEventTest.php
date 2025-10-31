<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\OrderPaidEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(OrderPaidEvent::class)]
final class OrderPaidEventTest extends AbstractEventTestCase
{
    public function testCanBeInstantiated(): void
    {
        $event = new OrderPaidEvent();
        $this->assertInstanceOf(OrderPaidEvent::class, $event);
    }

    public function testContractAwareFunctionality(): void
    {
        $contract = $this->createMock(Contract::class);
        $event = new OrderPaidEvent();

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }
}
