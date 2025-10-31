<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\SupplierExpireAcceptOrderEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(SupplierExpireAcceptOrderEvent::class)]
final class SupplierExpireAcceptOrderEventTest extends AbstractEventTestCase
{
    public function testContractAwareTrait(): void
    {
        $event = new SupplierExpireAcceptOrderEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testInheritsFromUserInteractionEvent(): void
    {
        $event = new SupplierExpireAcceptOrderEvent();
        $sender = $this->createMock(UserInterface::class);
        $receiver = $this->createMock(UserInterface::class);

        $event->setSender($sender);
        $event->setReceiver($receiver);

        $this->assertSame($sender, $event->getSender());
        $this->assertSame($receiver, $event->getReceiver());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new SupplierExpireAcceptOrderEvent();
        $this->assertInstanceOf(SupplierExpireAcceptOrderEvent::class, $event);
    }
}
