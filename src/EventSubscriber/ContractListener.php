<?php

namespace OrderCoreBundle\EventSubscriber;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Contract::class)]
final readonly class ContractListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(Contract $object): void
    {
        // 如果是后台创建的订单，默认就归属下单那个人
        if (null === $object->getUser() && null !== $this->security->getUser()) {
            $object->setUser($this->security->getUser());
        }

        // 如果是0元订单，那么我们要设置为已支付
        if ($object->getTotalAmount() <= 0 && OrderState::INIT === $object->getState()) {
            $object->setState(OrderState::PAID);
            $object->setPayTime(CarbonImmutable::now());
        }
    }
}
