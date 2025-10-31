<?php

namespace OrderCoreBundle\Service;

use OrderCoreBundle\Entity\Contract;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 履约服务
 */
interface ContractService
{
    /**
     * 创建订单
     */
    public function createOrder(Contract $contract): void;

    /**
     * 取消订单
     */
    public function cancelOrder(Contract $contract, ?UserInterface $user = null, ?string $cancelReason = null): void;

    /**
     * 支付订单
     */
    public function payOrder(Contract $contract): void;
}
