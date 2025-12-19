<?php

declare(strict_types=1);

namespace OrderCoreBundle\Param\Order;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPCPaginatorBundle\Param\PaginatorParamInterface;

final readonly class GetUserOrderListParam implements PaginatorParamInterface
{
    public function __construct(
        #[MethodParam(description: '订单编号')]
        public string $orderSn = '',

        #[MethodParam(description: '查询指定SPU ID的订单')]
        public string $spuId = '',

        #[MethodParam(description: '查询指定SKU ID的订单')]
        public string $skuId = '',

        /** @var array<string> SPU分类筛选 */
        #[MethodParam(description: 'SPU分类筛选')]
        public array $spuCategories = [],

        /** @var array<string> SPU类型筛选 */
        #[MethodParam(description: 'SPU类型筛选')]
        public array $spuTypes = [],

        /** @var array<string> 要过滤的订单状态列表 */
        #[MethodParam(description: '要过滤的订单状态列表')]
        public array $orderStates = [],

        #[MethodParam(description: '状态筛选（最好前端控制状态，不这样使用）')]
        public string $status = 'all',

        #[MethodParam(description: '下单日期-开始')]
        public string $createTimeBegin = '',

        #[MethodParam(description: '下单日期-结束')]
        public string $createTimeEnd = '',

        #[MethodParam(description: '门店id')]
        public string $storeId = '',

        #[MethodParam(description: '每页条数')]
        #[Assert\Range(min: 1, max: 2000)]
        public int $pageSize = 10,

        #[MethodParam(description: '当前页数')]
        #[Assert\Range(min: 1, max: 1000)]
        public int $currentPage = 1,

        #[MethodParam(description: '上一次拉取时，最后一条数据的主键ID')]
        public ?int $lastId = null,
    ) {
    }
}
