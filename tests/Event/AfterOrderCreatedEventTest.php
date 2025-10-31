<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\AfterOrderCreatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AfterOrderCreatedEvent::class)]
final class AfterOrderCreatedEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new AfterOrderCreatedEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testParamListSetterAndGetter(): void
    {
        $event = new AfterOrderCreatedEvent();
        $paramList = ['param1' => 'value1', 'param2' => 'value2'];

        $event->setParamList($paramList);
        $this->assertSame($paramList, $event->getParamList());
    }

    public function testParamListDefaultValue(): void
    {
        $event = new AfterOrderCreatedEvent();
        $this->assertSame([], $event->getParamList());
    }

    public function testRollbackSetterAndGetter(): void
    {
        $event = new AfterOrderCreatedEvent();

        $event->setRollback(true);
        $this->assertTrue($event->isRollback());

        $event->setRollback(false);
        $this->assertFalse($event->isRollback());
    }

    public function testRollbackDefaultValue(): void
    {
        $event = new AfterOrderCreatedEvent();
        $this->assertFalse($event->isRollback());
    }
}
