<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Param\Order\CancelUserProductParam;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Repository\OrderProductRepository;
use OrderCoreBundle\Service\OrderService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '消费者主动取消订单（单个商品）')]
#[MethodExpose(method: 'CancelUserProduct')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
final class CancelUserProduct extends LockableProcedure implements JsonRpcMethodInterface
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderProductRepository $productRepository,
        private readonly OrderService $orderService,
        private readonly EntityLockService $entityLockService,
        private readonly Security $security,
    ) {
    }

    /**
     * @phpstan-param CancelUserProductParam $param
     */
    public function execute(CancelUserProductParam|RpcParamInterface $param): ArrayResult
    {
        $contract = $this->contractRepository->findOneBy([
            'id' => $param->contractId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $contract) {
            throw new ApiException('找不到订单');
        }

        $this->entityLockService->lockEntity($contract, function () use ($contract, $param): void {
            $product = $this->productRepository->findOneBy([
                'contract' => $contract,
                'id' => $param->productId,
            ]);
            if (null === $product) {
                throw new ApiException('找不到产品信息');
            }

            $user = $this->security->getUser();
            if (null === $user) {
                throw new ApiException('用户未登录');
            }
            $this->orderService->cancelProduct($user, $product, $param->cancelReason);
        });

        return new ArrayResult([
            '__message' => '取消成功',
        ]);
    }
}
