<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Param\Order\ReceiveUserOrderParam;
use OrderCoreBundle\Repository\ContractRepository;
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
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '消费者主动收货（整单）')]
#[MethodExpose(method: 'ReceiveUserOrder')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
final class ReceiveUserOrder extends LockableProcedure implements JsonRpcMethodInterface
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderService $orderService,
        private readonly EntityLockService $entityLockService,
        private readonly Security $security,
    ) {
    }

    /**
     * @phpstan-param ReceiveUserOrderParam $param
     */
    public function execute(ReceiveUserOrderParam|RpcParamInterface $param): ArrayResult
    {
        $contract = $this->contractRepository->findOneBy([
            'id' => $param->contractId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $contract) {
            throw new ApiException('找不到订单');
        }

        $this->entityLockService->lockEntity($contract, function () use ($contract): void {
            $this->orderService->receiveOrder($contract, $this->security->getUser());
        });

        return new ArrayResult([
            '__message' => '收货成功',
        ]);
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '确认收货';
    }
}
