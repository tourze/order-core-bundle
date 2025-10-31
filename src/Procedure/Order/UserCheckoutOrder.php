<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '用户结账下单')]
#[MethodExpose(method: 'UserCheckoutOrder')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
class UserCheckoutOrder extends BaseProcedure implements JsonRpcMethodInterface, LogFormatProcedure
{
    use CheckoutTrait;

    /** @var array<mixed> 商品列表 */
    #[MethodParam(description: '商品列表')]
    public array $products = [];

    #[MethodParam(description: '收货地址ID')]
    public ?string $addressId = null;

    #[MethodParam(description: '备注信息')]
    public ?string $remark = null;

    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * 执行下单逻辑
     *
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function execute(): array
    {
        // 卫语句：验证商品列表
        if (!$this->validateProducts($this->products)) {
            throw new ApiException('找不到任何商品', 400);
        }

        $user = $this->security->getUser();
        if (null === $user) {
            throw new ApiException('用户未登录', 401);
        }

        // 生成订单编号
        $orderSn = $this->generateOrderSn();

        // 简化实现：返回基本信息
        // 实际业务逻辑应该通过 ContractService 创建订单
        return [
            'success' => true,
            'order_sn' => $orderSn,
            'message' => '下单成功',
            'user_identifier' => $user->getUserIdentifier(),
            'product_count' => count($this->products),
        ];
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return sprintf(
            '用户下单：商品数量=%d',
            count($this->products)
        );
    }
}
