<?php

namespace OrderCoreBundle\Event;

use OrderCoreBundle\Entity\Contract;
use Tourze\UserEventBundle\Event\UserInteractionEvent;

class ViewOrderEvent extends UserInteractionEvent
{
    /**
     * @var array<string, mixed> 订单查看结果数据
     */
    private array $result = [];

    private Contract $order;

    /**
     * @return array<string, mixed>
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array<string, mixed> $result
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function getOrder(): Contract
    {
        return $this->order;
    }

    public function setOrder(Contract $order): void
    {
        $this->order = $order;
    }
}
