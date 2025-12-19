<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Event\BeforeOrderProductRefundEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(BeforeOrderProductRefundEvent::class)]
final class BeforeOrderProductRefundEventTest extends AbstractEventTestCase
{
    public function testContractSetterAndGetter(): void
    {
        $event = new BeforeOrderProductRefundEvent();
        $contract = $this->createMock(Contract::class);

        $event->setContract($contract);
        $this->assertSame($contract, $event->getContract());
    }

    public function testProductSetterAndGetter(): void
    {
        $event = new BeforeOrderProductRefundEvent();
        $product = $this->createMock(OrderProduct::class);

        $event->setProduct($product);
        $this->assertSame($product, $event->getProduct());
    }

    public function testCanBeInstantiated(): void
    {
        $event = new BeforeOrderProductRefundEvent();
        $this->assertInstanceOf(BeforeOrderProductRefundEvent::class, $event);
    }
}
