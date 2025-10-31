<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\OrderReceivedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(OrderReceivedEvent::class)]
final class OrderReceivedEventTest extends AbstractEventTestCase
{
    public function testCanBeInstantiated(): void
    {
        $event = new OrderReceivedEvent();
        $this->assertInstanceOf(OrderReceivedEvent::class, $event);
    }

    public function testContractAwareFunctionality(): void
    {
        $contract = $this->createMock(Contract::class);
        $event = new OrderReceivedEvent();

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }
}
