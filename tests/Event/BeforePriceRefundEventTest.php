<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Event\BeforePriceRefundEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(BeforePriceRefundEvent::class)]
final class BeforePriceRefundEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new BeforePriceRefundEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testPriceSetterAndGetter(): void
    {
        $event = new BeforePriceRefundEvent();
        $price = $this->createMock(OrderPrice::class);

        $event->setPrice($price);
        $this->assertSame($price, $event->getPrice());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new BeforePriceRefundEvent();
        $this->assertInstanceOf(BeforePriceRefundEvent::class, $event);
    }
}
