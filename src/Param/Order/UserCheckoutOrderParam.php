<?php

declare(strict_types=1);

namespace OrderCoreBundle\Param\Order;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

final readonly class UserCheckoutOrderParam implements RpcParamInterface
{
    public function __construct(
        /** @var array<mixed> 商品列表 */
        #[MethodParam(description: '商品列表')]
        #[Assert\NotBlank(message: '商品列表不能为空')]
        public array $products = [],

        #[MethodParam(description: '收货地址ID')]
        public ?string $addressId = null,

        #[MethodParam(description: '备注信息')]
        public ?string $remark = null,
    ) {
    }
}
