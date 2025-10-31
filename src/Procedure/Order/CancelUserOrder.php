<?php

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Service\ContractService;
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
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '消费者主动取消订单（整单）')]
#[MethodExpose(method: 'CancelUserOrder')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
class CancelUserOrder extends LockableProcedure implements LogFormatProcedure
{
    #[MethodParam(description: '订单ID')]
    public string $contractId;

    #[MethodParam(description: '取消原因')]
    public ?string $cancelReason = null;

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly ContractService $contractService,
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

        /** @var array<string, mixed> */
        return $this->entityLockService->lockEntity($contract, function () use ($contract): array {
            if (OrderState::CANCELED === $contract->getState()) {
                return [
                    '__message' => '取消成功',
                ];
            }

            $this->contractService->cancelOrder($contract, $this->security->getUser(), $this->cancelReason);

            return [
                '__message' => '取消成功',
            ];
        });
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '消费者主动取消订单';
    }
}
