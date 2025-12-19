<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\CreateOrderFailedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(CreateOrderFailedEvent::class)]
final class CreateOrderFailedEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new CreateOrderFailedEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testUserInteractionEventProperties(): void
    {
        $event = new CreateOrderFailedEvent();
        $sender = $this->createMock(UserInterface::class);
        $receiver = $this->createMock(UserInterface::class);

        $event->setSender($sender);
        $event->setReceiver($receiver);

        $this->assertSame($sender, $event->getSender());
        $this->assertSame($receiver, $event->getReceiver());
    }
}
