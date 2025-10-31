<?php

namespace OrderCoreBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use OrderCoreBundle\Entity\Contract;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Contract::class)]
class ContractListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(Contract $object): void
    {
        // 如果是后台创建的订单，默认就归属下单那个人
        if (null === $object->getUser() && null !== $this->security->getUser()) {
            $object->setUser($this->security->getUser());
        }
    }
}
