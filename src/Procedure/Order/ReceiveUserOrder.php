<?php

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Repository\ContractRepository;
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
#[MethodDoc(summary: '消费者主动收货（整单）')]
#[MethodExpose(method: 'ReceiveUserOrder')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
class ReceiveUserOrder extends LockableProcedure implements JsonRpcMethodInterface
{
    #[MethodParam(description: '订单ID')]
    public string $contractId;

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderService $orderService,
        private readonly EntityLockService $entityLockService,
        private readonly Security $security,
    ) {
    }

    public static function getMockResult(): ?array
    {
        return [
            '__message' => '收货成功',
        ];
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
            $this->orderService->receiveOrder($contract, $this->security->getUser());
        });

        return [
            '__message' => '收货成功',
        ];
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '确认收货';
    }
}
