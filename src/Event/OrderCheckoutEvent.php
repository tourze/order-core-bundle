<?php

namespace OrderCoreBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;

class OrderCheckoutEvent extends UserInteractionEvent
{
    use ContractAware;

    /**
     * @var array<string, mixed> 订单结算结果数据
     */
    private array $result = [];

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
}
