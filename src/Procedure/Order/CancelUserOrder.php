<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Param\Order\CancelUserOrderParam;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Service\ContractService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '消费者主动取消订单（整单）')]
#[MethodExpose(method: 'CancelUserOrder')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
final class CancelUserOrder extends LockableProcedure implements LogFormatProcedure
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly ContractService $contractService,
        private readonly EntityLockService $entityLockService,
        private readonly Security $security,
    ) {
    }

    /**
     * @phpstan-param CancelUserOrderParam $param
     */
    public function execute(CancelUserOrderParam|RpcParamInterface $param): ArrayResult
    {
        $contract = $this->contractRepository->findOneBy([
            'id' => $param->contractId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $contract) {
            throw new ApiException('找不到订单');
        }

        return $this->entityLockService->lockEntity($contract, function () use ($contract, $param): ArrayResult {
            if (OrderState::CANCELED === $contract->getState()) {
                return new ArrayResult([
                    '__message' => '取消成功',
                ]);
            }

            $this->contractService->cancelOrder($contract, $this->security->getUser(), $param->cancelReason);

            return new ArrayResult([
                '__message' => '取消成功',
            ]);
        });
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '消费者主动取消订单';
    }
}
