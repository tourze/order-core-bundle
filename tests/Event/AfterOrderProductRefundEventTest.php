<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Event\AfterOrderProductRefundEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(AfterOrderProductRefundEvent::class)]
final class AfterOrderProductRefundEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new AfterOrderProductRefundEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testProductSetterAndGetter(): void
    {
        $event = new AfterOrderProductRefundEvent();
        $product = $this->createMock(OrderProduct::class);

        $event->setProduct($product);
        $this->assertSame($product, $event->getProduct());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new AfterOrderProductRefundEvent();
        $this->assertInstanceOf(AfterOrderProductRefundEvent::class, $event);
    }
}
