<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\OrderListStatusFilterEvent;
use OrderCoreBundle\Param\Order\GetUserOrderListParam;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Service\PriceService;
use OrderCoreBundle\Service\ProductCoreServiceWrapper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '获取用户所有的订单列表')]
#[MethodExpose(method: 'GetUserOrderList')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
final class GetUserOrderList extends BaseProcedure implements JsonRpcMethodInterface
{
    use PaginatorTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ContractRepository $contractRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProductCoreServiceWrapper $productService,
        private readonly PriceService $priceService,
    ) {
    }

    /**
     * @phpstan-param GetUserOrderListParam $param
     */
    public function execute(GetUserOrderListParam|RpcParamInterface $param): ArrayResult
    {
        $qb = $this->createBaseQuery();
        $this->applyProductFilters($qb, $param);
        $this->applyBasicFilters($qb, $param);
        $this->dispatchFilterEvent($qb, $param);
        $this->applyStatusFilter($qb, $param);

        return new ArrayResult($this->fetchList($qb, $this->formatItem(...), null, $param));
    }

    /**
     * 创建基础查询
     */
    private function createBaseQuery(): QueryBuilder
    {
        return $this->contractRepository
            ->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $this->security->getUser())
            ->orderBy('a.id', Order::Descending->value)
        ;
    }

    /**
     * 应用产品筛选条件
     */
    private function applyProductFilters(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        $this->applySpuFilter($qb, $param);
        $this->applySkuFilter($qb, $param);
        $this->applySpuTypeFilter($qb, $param);
        // SPU分类筛选暂时未实现
    }

    /**
     * 应用SPU筛选
     */
    private function applySpuFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ('' === $param->spuId) {
            return;
        }

        $spu = $this->productService->findSpuById($param->spuId);
        if (null !== $spu) {
            $qb->join('a.products', 'pp');
            $qb->andWhere('pp.spu = :spu');
            $qb->setParameter('spu', $spu);
        }
    }

    /**
     * 应用SKU筛选
     */
    private function applySkuFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ('' === $param->skuId) {
            return;
        }

        $sku = $this->productService->findSkuById($param->skuId);
        if (null !== $sku) {
            $qb->join('a.products', 'pk');
            $qb->andWhere('pk.sku = :sku');
            $qb->setParameter('sku', $sku);
        }
    }

    /**
     * 应用SPU类型筛选
     */
    private function applySpuTypeFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ([] === $param->spuTypes) {
            return;
        }

        $qb->join('a.products', 'p');
        $qb->join('p.spu', 's');
        $qb->where('s.type IN (:spuTypes)');
        $qb->setParameter('spuTypes', $param->spuTypes);
    }

    /**
     * 应用基础筛选条件
     */
    private function applyBasicFilters(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        $this->applyOrderSnFilter($qb, $param);
        $this->applyTimeRangeFilter($qb, $param);
        $this->applyOrderStatesFilter($qb, $param);
        $this->applyStoreFilter($qb, $param);
    }

    /**
     * 应用订单编号筛选
     */
    private function applyOrderSnFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ('' !== $param->orderSn) {
            $qb->andWhere('a.sn LIKE :likeSN');
            $qb->setParameter('likeSN', "%{$param->orderSn}%");
        }
    }

    /**
     * 应用时间范围筛选
     */
    private function applyTimeRangeFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ('' !== $param->createTimeBegin) {
            $qb->andWhere('a.createTime > :createTimeBegin');
            $qb->setParameter('createTimeBegin', CarbonImmutable::parse($param->createTimeBegin));
        }

        if ('' !== $param->createTimeEnd) {
            $qb->andWhere('a.createTime < :createTimeEnd');
            $qb->setParameter('createTimeEnd', CarbonImmutable::parse($param->createTimeEnd));
        }
    }

    /**
     * 应用订单状态筛选
     */
    private function applyOrderStatesFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ([] !== $param->orderStates) {
            $qb->andWhere('a.state IN (:states)');
            $qb->setParameter('states', $param->orderStates);
        }
    }

    /**
     * 应用门店筛选
     */
    private function applyStoreFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        if ('' !== $param->storeId) {
            $qb->andWhere('a.store = :store');
            $qb->setParameter('store', $param->storeId);
        }
    }

    /**
     * 分发筛选事件
     */
    private function dispatchFilterEvent(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        $event = new OrderListStatusFilterEvent();
        $event->setQueryBuilder($qb);
        $event->setUser($this->security->getUser());
        $event->setStatus($param->status);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * 应用状态筛选（消除特殊情况）
     */
    private function applyStatusFilter(QueryBuilder $qb, GetUserOrderListParam $param): void
    {
        // 卸语句：不需要处理的情况
        if ('all' === $param->status) {
            return;
        }

        $statusMappings = $this->getStatusMappings();
        if (!isset($statusMappings[$param->status])) {
            return;
        }

        $states = $statusMappings[$param->status];
        if (1 === count($states)) {
            $qb->andWhere('a.state = :state');
            $qb->setParameter('state', $states[0]);
        } else {
            $qb->andWhere('a.state IN (:state)');
            $qb->setParameter('state', $states);
        }
    }

    /**
     * 获取状态映射表（数据结构优先）
     */
    /** @return array<string, array<OrderState>> */
    private function getStatusMappings(): array
    {
        return new ArrayResult([
            'unpaid' => [
                OrderState::AUDITING,
                OrderState::INIT,
                OrderState::PAYING,
            ],
            'paid' => [OrderState::PAID],
            'sent' => [
                OrderState::SHIPPED,
                OrderState::PART_SHIPPED,
            ],
            'finished' => [
                OrderState::RECEIVED,
                OrderState::CANCELED,
            ],
            'back' => [
                OrderState::AFTERSALES_ING,
                OrderState::AFTERSALES_SUCCESS,
                OrderState::AFTERSALES_FAILED,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function formatItem(Contract $item): array
    {
        // 获取基础订单信息
        $result = $item->retrieveApiArray();

        $prices = $this->priceService->calculateTotalPricesByType($item, true);
        $result['price'] = $prices['total'] ?? '0.00';
        // 添加用户信息
        $result['user'] = $this->formatUserInfo($item->getUser());

        // 添加商品信息
        $result['products'] = $this->formatProductsInfo($item->getProducts());

        // 添加价格信息
        $result['prices'] = $prices;

        // 添加联系人/地址信息
        $result['contacts'] = $this->formatContactsInfo($item->getContacts());

        return new ArrayResult($result);
    }

    /** @return array<string, mixed>|null */
    private function formatUserInfo(?object $user): ?array
    {
        if (null === $user) {
            return null;
        }

        $userInfo = $this->normalizer->normalize($user, 'array', ['groups' => 'restful_read']);
        if (!is_array($userInfo)) {
            return null;
        }

        // 只返回安全的用户信息，移除敏感数据
        return new ArrayResult([
            'id' => $userInfo['id'] ?? null,
            'username' => $userInfo['username'] ?? null,
            'email' => $userInfo['email'] ?? null,
            // 不返回密码等敏感信息
        ]);
    }

    /**
     * @param Collection<int, OrderProduct> $products
     * @return array<int, array<string, mixed>>
     */
    private function formatProductsInfo(Collection $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $productInfo = $this->normalizer->normalize($product, 'array', ['groups' => 'restful_read']);
            if (!is_array($productInfo)) {
                continue;
            }
            // 添加 SKU 和 SPU 的详细信息
            $price = $product->getTotalPrice();
            $productInfo['sku_details'] = $this->formatSkuInfo($product->getSku());
            $productInfo['spu_details'] = $this->formatSpuInfo($product->getSpu());
            $productInfo['integralPrice'] = $product->getIntegralPrice();
            $productInfo['price'] = number_format($price, 2);
            $productInfo['mainThumb'] = $product->getSku()?->getMainThumb() ?? $product->getSpu()?->getMainPic() ?? null;
            $result[] = $productInfo;
        }

        /** @var array<int, array<string, mixed>> $result */
        return new ArrayResult($result);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatSkuInfo(?object $sku): ?array
    {
        if (null === $sku) {
            return null;
        }

        $skuInfo = $this->normalizer->normalize($sku, 'array', ['groups' => 'restful_read']);

        /** @var array<string, mixed>|null */
        return is_array($skuInfo) ? $skuInfo : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatSpuInfo(?object $spu): ?array
    {
        if (null === $spu) {
            return null;
        }

        $spuInfo = $this->normalizer->normalize($spu, 'array', ['groups' => 'restful_read']);

        /** @var array<string, mixed>|null */
        return is_array($spuInfo) ? $spuInfo : null;
    }

    /**
     * @param Collection<int, OrderContact> $contacts
     * @return array<int, array<string, mixed>>
     */
    private function formatContactsInfo(Collection $contacts): array
    {
        $result = [];
        foreach ($contacts as $contact) {
            $contactInfo = $contact->retrieveApiArray();
            if (is_array($contactInfo)) {
                $result[] = $contactInfo;
            }
        }

        return new ArrayResult($result);
    }
}
