<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Counter;

use CounterBundle\Repository\CounterRepository;
use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Counter\OrderDailyCounterProvider;
use OrderCoreBundle\Repository\ContractRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(OrderDailyCounterProvider::class)]
class OrderDailyCounterProviderTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $contractRepository = $this->createMock(ContractRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $counterRepository = $this->createMock(CounterRepository::class);

        $provider = new OrderDailyCounterProvider($contractRepository, $entityManager, $counterRepository);
        $this->assertInstanceOf(OrderDailyCounterProvider::class, $provider);
    }

    public function testHasCorrectProviderKey(): void
    {
        $contractRepository = $this->createMock(ContractRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $counterRepository = $this->createMock(CounterRepository::class);

        $provider = new OrderDailyCounterProvider($contractRepository, $entityManager, $counterRepository);
        $result = $provider->getCounters();

        $this->assertIsIterable($result);
    }

    public function testProvideReturnsIterable(): void
    {
        $contractRepository = $this->createMock(ContractRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $counterRepository = $this->createMock(CounterRepository::class);

        $provider = new OrderDailyCounterProvider($contractRepository, $entityManager, $counterRepository);
        $counters = $provider->getCounters();

        $this->assertIsIterable($counters);
    }
}
