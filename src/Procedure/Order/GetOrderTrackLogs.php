<?php

declare(strict_types=1);

namespace OrderCoreBundle\Procedure\Order;

use Doctrine\Common\Collections\Order;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Param\Order\GetOrderTrackLogsParam;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Repository\OrderLogRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '获取订单状态追踪日志')]
#[MethodExpose(method: 'GetOrderTrackLogs')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
final class GetOrderTrackLogs extends LockableProcedure implements JsonRpcMethodInterface
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderLogRepository $orderLogRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @phpstan-param GetOrderTrackLogsParam $param
     */
    public function execute(GetOrderTrackLogsParam|RpcParamInterface $param): ArrayResult
    {
        $order = $this->contractRepository->findOneBy([
            'id' => $param->orderId,
            'user' => $this->security->getUser(),
        ]);
        if (null === $order) {
            $order = $this->contractRepository->findOneBy([
                'sn' => $param->orderId,
                'user' => $this->security->getUser(),
            ]);
        }

        if (null === $order) {
            throw new ApiException('找不到订单');
        }

        $logs = $this->orderLogRepository
            ->createQueryBuilder('a')
            ->andWhere('a.contract = :order')
            ->setParameter('order', $order)
            ->orderBy('a.id', Order::Descending->value)
            ->getQuery()
            ->toIterable()
        ;

        $items = [];
        foreach ($logs as $log) {
            /** @var OrderLog $log */
            $tmp = [
                'id' => $log->getId(),
                'currentState' => $log->getCurrentState()?->getLabel(),
                'createTime' => $log->getCreateTime()?->format('Y-m-d H:i:s') ?? '',
            ];
            $items[] = $tmp;
        }

        return new ArrayResult([
            'items' => $items,
        ]);
    }
}
