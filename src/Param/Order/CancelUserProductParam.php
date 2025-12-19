<?php

declare(strict_types=1);

namespace OrderCoreBundle\Param\Order;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

final readonly class CancelUserProductParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '订单ID')]
        #[Assert\NotBlank(message: '订单ID不能为空')]
        public string $contractId,

        #[MethodParam(description: '商品行ID')]
        #[Assert\NotBlank(message: '商品行ID不能为空')]
        public string $productId,

        #[MethodParam(description: '取消原因')]
        public ?string $cancelReason = null,
    ) {
    }
}
