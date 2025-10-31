<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Event\ViewOrderEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(ViewOrderEvent::class)]
final class ViewOrderEventTest extends AbstractEventTestCase
{
    public function testResultSetterAndGetter(): void
    {
        $event = new ViewOrderEvent();
        $result = ['key1' => 'value1', 'key2' => 'value2'];

        $event->setResult($result);
        $this->assertSame($result, $event->getResult());
    }

    public function testResultDefaultValue(): void
    {
        $event = new ViewOrderEvent();
        $this->assertSame([], $event->getResult());
    }

    public function testOrderSetterAndGetter(): void
    {
        $event = new ViewOrderEvent();
        $order = $this->createMock(Contract::class);

        $event->setOrder($order);
        $this->assertSame($order, $event->getOrder());
    }
}
