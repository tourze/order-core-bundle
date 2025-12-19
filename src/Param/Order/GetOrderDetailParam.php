<?php

declare(strict_types=1);

namespace OrderCoreBundle\Param\Order;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

final readonly class GetOrderDetailParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '订单ID或SN')]
        #[Assert\NotBlank(message: '订单ID或SN不能为空')]
        public string $orderId = '',
    ) {
    }
}
