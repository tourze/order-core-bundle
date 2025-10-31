<?php

namespace OrderCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * 订单被创建前，触发该事件
 */
class BeforeOrderCreatedEvent extends Event
{
    use ContractAware;

    /**
     * @var array<string, mixed> 订单创建参数列表
     */
    private array $paramList = [];

    /**
     * @return array<string, mixed>
     */
    public function getParamList(): array
    {
        return $this->paramList;
    }

    /**
     * @param array<string, mixed> $paramList
     */
    public function setParamList(array $paramList): void
    {
        $this->paramList = $paramList;
    }
}
