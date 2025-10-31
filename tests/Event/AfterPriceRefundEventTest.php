<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Event\AfterPriceRefundEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AfterPriceRefundEvent::class)]
final class AfterPriceRefundEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new AfterPriceRefundEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testPriceSetterAndGetter(): void
    {
        $event = new AfterPriceRefundEvent();
        $price = $this->createMock(OrderPrice::class);

        $event->setPrice($price);
        $this->assertSame($price, $event->getPrice());
    }
}
