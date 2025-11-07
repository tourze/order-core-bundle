<?php

namespace OrderCoreBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\AutoExpireOrderStateEvent;
use OrderCoreBundle\Repository\ContractRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Tourze\UserServiceContracts\UserManagerInterface;

#[AsCronTask(expression: '*/15 * * * *')]
#[AsCommand(name: self::NAME, description: '将发货但有结束收货时间的订单拉出来处理')]
class ExpireNoReceivedOrderCommand extends Command
{
    public const NAME = 'order:expire-no-received';

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManagerInterface $userManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var iterable<Contract> $orders */
        $orders = $this->contractRepository->createQueryBuilder('a')
            ->andWhere('a.state IN (:states) AND a.expireReceiveTime IS NOT NULL AND a.expireReceiveTime <= :now')
            ->setParameter('states', [
                OrderState::PAID,
            ])
            ->setParameter('now', CarbonImmutable::now())
            ->getQuery()
            ->toIterable()
        ;
        foreach ($orders as $order) {
            if (!$order instanceof Contract) {
                continue;
            }

            $order->setState(OrderState::EXPIRED);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $output->writeln(sprintf('订单%s已过期', $order->getSn()));

            $user = $order->getUser();
            if (null !== $user) {
                // Create a system user for automated operations
                $systemUser = $this->userManager->createUser(
                    userIdentifier: 'system',
                    password: '',
                    roles: ['ROLE_SYSTEM']
                );

                $event = new AutoExpireOrderStateEvent();
                $event->setContract($order);
                $event->setSender($systemUser);
                $event->setReceiver($user);
                $this->eventDispatcher->dispatch($event);
            }
        }

        return Command::SUCCESS;
    }
}
