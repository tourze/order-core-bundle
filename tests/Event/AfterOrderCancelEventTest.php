<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AfterOrderCancelEvent::class)]
final class AfterOrderCancelEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new AfterOrderCancelEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testUserInteractionEventProperties(): void
    {
        $event = new AfterOrderCancelEvent();
        $sender = $this->createMock(UserInterface::class);
        $receiver = $this->createMock(UserInterface::class);

        $event->setSender($sender);
        $event->setReceiver($receiver);

        $this->assertSame($sender, $event->getSender());
        $this->assertSame($receiver, $event->getReceiver());
    }
}
