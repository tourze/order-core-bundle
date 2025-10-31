<?php

namespace OrderCoreBundle\Procedure\Order;

use Doctrine\Common\Collections\Collection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\ViewOrderEvent;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Service\PriceService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\OrderContracts\Event\CheckOrderRefundableEvent;
use Tourze\OrderContracts\Event\GetOrderDetailEvent;
use Tourze\UserIDBundle\Model\SystemUser;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '获取单个订单的信息')]
#[MethodExpose(method: 'GetOrderDetail')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetOrderDetail extends BaseProcedure
{
    #[MethodParam(description: '订单ID或SN')]
    public string $orderId = '';

    public function __construct(
        private readonly Security $security,
        private readonly ContractRepository $contractRepository,
        private readonly NormalizerInterface $normalizer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PriceService $priceService,
    ) {
    }

    public function execute(): array
    {
        if ('' === $this->orderId) {
            throw new ApiException('参数错误，订单ID不能为空');
        }
        $order = $this->contractRepository->findOneBy([
            'id' => $this->orderId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $order) {
            $order = $this->contractRepository->findOneBy([
                'sn' => $this->orderId,
                'user' => $this->security->getUser(),
            ]);
        }

        if (null === $order) {
            throw new ApiException('找不到订单');
        }

        $result = $this->formatOrderDetail($order);

        if (OrderState::EXCEPTION === $order->getState()) {
            $result['statusText'] = '订单支付失败，请重新下单';
        }

        $event = new ViewOrderEvent();
        $event->setOrder($order);
        $event->setResult($result);

        $currentUser = $this->security->getUser();
        if (null !== $currentUser) {
            $event->setSender($currentUser);
        }
        $event->setReceiver(SystemUser::instance());
        $this->eventDispatcher->dispatch($event);

        return $event->getResult();
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '查看订单详情';
    }

    /** @return array<string, mixed> */
    private function formatOrderDetail(Contract $order): array
    {
        // 获取基础订单信息
        $result = $order->retrieveApiArray();

        $prices = $this->priceService->calculateTotalPricesByType($order, true);

        // 添加用户信息
        $result['price'] = $order->getTotalAmount();
        $result['user'] = $this->formatUserInfo($order->getUser());

        // 添加商品信息
        $result['products'] = $this->formatProductsInfo($order->getProducts());

        // 添加价格信息
        $result['prices'] = $prices;

        // 添加联系人/地址信息
        $result['contacts'] = $this->formatContactsInfo($order->getContacts());

        // 处理售后状态
        return $this->processAftersalesStatus($result, $order);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function processAftersalesStatus(array $result, Contract $order): array
    {
        // 获取产品级别的售后状态
        $event = new GetOrderDetailEvent();
        $event->setOrderId($order->getSn());
        $this->eventDispatcher->dispatch($event);
        $aftersalesStatus = $event->getAftersalesStatus();

        $canRefund = false;
        foreach ($result['products'] as $key => $product) {
            $productCanRefund = $this->checkProductRefundable($product, $aftersalesStatus);
            $result['products'][$key]['canRefund'] = $productCanRefund;
            if ($productCanRefund) {
                $canRefund = true;
            }
        }

        $result['canRefund'] = $canRefund;

        return $result;
    }

    /** @param array<string, array<string>>|null $aftersalesStatus */
    private function checkProductRefundable(mixed $product, ?array $aftersalesStatus): bool
    {
        if (!is_array($product)) {
            return true;
        }

        $productId = $product['id'] ?? null;
        if (null === $productId || !is_scalar($productId)) {
            return true;
        }

        if (null === $aftersalesStatus) {
            return true;
        }

        $productIdString = (string) $productId;
        if (!isset($aftersalesStatus[$productIdString])) {
            return true;
        }

        return '' === $aftersalesStatus[$productIdString];
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
        return [
            'id' => $userInfo['id'] ?? null,
            'username' => $userInfo['username'] ?? null,
            'email' => $userInfo['email'] ?? null,
            // 不返回密码等敏感信息
        ];
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
            $productInfo['canRefund'] = false;
            $productInfo['price'] = $price;
            $spu = $product->getSpu();
            $sku = $product->getSku();
            $mainThumb = $sku?->getMainThumb() ?? $spu?->getMainPic() ?? null;
            $productInfo['mainThumb'] = $mainThumb;
            $result[] = $productInfo;
        }

        /** @var array<int, array<string, mixed>> $result */
        return $result;
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

        return $result;
    }
}
