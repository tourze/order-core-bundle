<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\BeforeOrderCreatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(BeforeOrderCreatedEvent::class)]
final class BeforeOrderCreatedEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new BeforeOrderCreatedEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testParamListSetterAndGetter(): void
    {
        $event = new BeforeOrderCreatedEvent();
        $paramList = ['param1' => 'value1', 'param2' => 'value2'];

        $event->setParamList($paramList);
        $this->assertSame($paramList, $event->getParamList());
    }

    public function testParamListDefaultValue(): void
    {
        $event = new BeforeOrderCreatedEvent();
        $this->assertSame([], $event->getParamList());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new BeforeOrderCreatedEvent();
        $this->assertInstanceOf(BeforeOrderCreatedEvent::class, $event);
    }
}
