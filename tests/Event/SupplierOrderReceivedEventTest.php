<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\SupplierOrderReceivedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(SupplierOrderReceivedEvent::class)]
final class SupplierOrderReceivedEventTest extends AbstractEventTestCase
{
    public function testContractAwareTrait(): void
    {
        $event = new SupplierOrderReceivedEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testInheritsFromUserInteractionEvent(): void
    {
        $event = new SupplierOrderReceivedEvent();
        $sender = $this->createMock(UserInterface::class);
        $receiver = $this->createMock(UserInterface::class);

        $event->setSender($sender);
        $event->setReceiver($receiver);

        $this->assertSame($sender, $event->getSender());
        $this->assertSame($receiver, $event->getReceiver());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new SupplierOrderReceivedEvent();
        $this->assertInstanceOf(SupplierOrderReceivedEvent::class, $event);
    }
}
