<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Event;

use Doctrine\ORM\QueryBuilder;
use OrderCoreBundle\Event\OrderListStatusFilterEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(OrderListStatusFilterEvent::class)]
final class OrderListStatusFilterEventTest extends AbstractEventTestCase
{
    public function testStatusSetterAndGetter(): void
    {
        $event = new OrderListStatusFilterEvent();
        $status = 'pending';

        $event->setStatus($status);
        $this->assertSame($status, $event->getStatus());
    }

    public function testUserSetterAndGetter(): void
    {
        $event = new OrderListStatusFilterEvent();
        $user = $this->createMock(UserInterface::class);

        $event->setUser($user);
        $this->assertSame($user, $event->getUser());
    }

    public function testUserCanBeNull(): void
    {
        $event = new OrderListStatusFilterEvent();

        $event->setUser(null);
        $this->assertNull($event->getUser());
    }

    public function testQueryBuilderSetterAndGetter(): void
    {
        $event = new OrderListStatusFilterEvent();
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $event->setQueryBuilder($queryBuilder);
        $this->assertSame($queryBuilder, $event->getQueryBuilder());
    }

    public function testQueryBuilderCanBeNull(): void
    {
        $event = new OrderListStatusFilterEvent();

        $event->setQueryBuilder(null);
        $this->assertNull($event->getQueryBuilder());
    }

    public function testDefaultValues(): void
    {
        $event = new OrderListStatusFilterEvent();

        $this->assertNull($event->getUser());
        $this->assertNull($event->getQueryBuilder());
    }
}
