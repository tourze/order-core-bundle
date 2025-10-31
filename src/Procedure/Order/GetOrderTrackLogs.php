<?php

namespace OrderCoreBundle\Procedure\Order;

use Doctrine\Common\Collections\Order;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Repository\OrderLogRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;

#[MethodTag(name: '订单管理')]
#[MethodDoc(summary: '获取订单状态追踪日志')]
#[MethodExpose(method: 'GetOrderTrackLogs')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetOrderTrackLogs extends LockableProcedure implements JsonRpcMethodInterface
{
    #[MethodParam(description: '订单ID或SN')]
    public string $orderId;

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly OrderLogRepository $orderLogRepository,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
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

        return [
            'items' => $items,
        ];
    }
}
