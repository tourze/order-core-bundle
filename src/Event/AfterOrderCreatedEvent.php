<?php

namespace OrderCoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterOrderCreatedEvent extends Event
{
    use ContractAware;

    /**
     * @var array<string, mixed> 订单创建参数列表
     */
    private array $paramList = [];

    /**
     * @var bool 是否需要回滚
     */
    private bool $rollback = false;

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

    public function isRollback(): bool
    {
        return $this->rollback;
    }

    public function setRollback(bool $rollback): void
    {
        $this->rollback = $rollback;
    }
}
