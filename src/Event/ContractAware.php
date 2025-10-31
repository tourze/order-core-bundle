<?php

namespace OrderCoreBundle\Event;

use OrderCoreBundle\Entity\Contract;

trait ContractAware
{
    private Contract $contract;

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function setContract(Contract $contract): void
    {
        $this->contract = $contract;
    }
}
