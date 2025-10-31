<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(OrderLog::class)]
final class OrderLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OrderLog();
    }

    public function testCanBeInstantiated(): void
    {
        $entity = new OrderLog();
        $this->assertInstanceOf(OrderLog::class, $entity);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    /** @return \Generator<string, array{string, mixed}> */
    public static function propertiesProvider(): \Generator
    {
        yield 'currentState' => ['currentState', OrderState::PAID];
        yield 'orderSn' => ['orderSn', 'ORDER123456'];
        yield 'action' => ['action', 'payment'];
        yield 'description' => ['description', 'Order payment received'];
        yield 'level' => ['level', 'info'];
        yield 'context' => ['context', ['payment_method' => 'credit_card']];
        yield 'ipAddress' => ['ipAddress', '192.168.1.1'];
        yield 'userAgent' => ['userAgent', 'Mozilla/5.0 Test Browser'];
    }
}
