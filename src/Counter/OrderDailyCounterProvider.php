<?php

namespace OrderCoreBundle\Counter;

use Carbon\CarbonImmutable;
use CounterBundle\Entity\Counter;
use CounterBundle\Provider\CounterProvider;
use CounterBundle\Repository\CounterRepository;
use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Repository\ContractRepository;

/**
 * 每日订单数的统计
 */
readonly class OrderDailyCounterProvider implements CounterProvider
{
    public function __construct(
        private ContractRepository $contractRepository,
        private EntityManagerInterface $entityManager,
        private CounterRepository $counterRepository,
    ) {
    }

    public function getCounters(): iterable
    {
        $now = CarbonImmutable::now();
        $key = Contract::LOCK_PREFIX . $now->format('Ymd');
        $startTime = $now->startOfDay();
        $endTime = $now->endOfDay();

        $counter = $this->counterRepository->findOneBy(['name' => $key]);
        if (null === $counter) {
            $counter = new Counter();
            $counter->setName($key);
        }
        $counter->setCount($this->contractRepository->countByCreateTimeDateRange($startTime, $endTime));
        $this->entityManager->persist($counter);
        $this->entityManager->flush();

        yield $counter;
    }
}
