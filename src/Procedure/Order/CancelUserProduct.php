<?php

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Repository\OrderProductRepository;
use OrderCoreBundle\Service\OrderService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '消费者主动取消订单（单个商品）')]
#[MethodExpose(method: 'CancelUserProduct')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
class CancelUserProduct extends LockableProcedure implements JsonRpcMethodInterface
{
    #[MethodParam(description: '订单ID')]
    public string $contractId;

    #[MethodParam(description: '商品行ID')]
    public string $productId;

    #[MethodParam(description: '取消原因')]
    public ?string $cancelReason = null;

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderProductRepository $productRepository,
        private readonly OrderService $orderService,
        private readonly EntityLockService $entityLockService,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $contract = $this->contractRepository->findOneBy([
            'id' => $this->contractId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $contract) {
            throw new ApiException('找不到订单');
        }

        $this->entityLockService->lockEntity($contract, function () use ($contract): void {
            $product = $this->productRepository->findOneBy([
                'contract' => $contract,
                'id' => $this->productId,
            ]);
            if (null === $product) {
                throw new ApiException('找不到产品信息');
            }

            $user = $this->security->getUser();
            if (null === $user) {
                throw new ApiException('用户未登录');
            }
            $this->orderService->cancelProduct($user, $product, $this->cancelReason);
        });

        return [
            '__message' => '取消成功',
        ];
    }
}
