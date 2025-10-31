<?php

namespace OrderCoreBundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Event\AfterOrderCancelEvent;
use OrderCoreBundle\Event\AfterOrderCreatedEvent;
use OrderCoreBundle\Event\BeforeOrderCreatedEvent;
use OrderCoreBundle\Event\OrderPaidEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\DoctrineEntityLockBundle\Service\EntityLockService;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\StockManageBundle\Entity\StockLog;
use Tourze\StockManageBundle\Enum\StockChange;
use Tourze\StockManageBundle\Service\StockService;

/**
 * 订单中的库存处理
 */
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'order_core')]
readonly class StockSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private ?EntityLockService $entityLockService = null,
        private ?StockService $stockService = null,
    ) {
    }

    /**
     * 订单支付前，我们检查一下库存是否充足
     *
     * @throws ApiException
     */
    #[AsEventListener]
    public function onBeforeOrderCreated(BeforeOrderCreatedEvent $event): void
    {
        if (null === $this->stockService) {
            return;
        }

        // 要购买的数量，大于当前库存数量，则无法继续
        // TODO 要注意这里我们还没加锁的喔
        foreach ($event->getContract()->getProducts() as $product) {
            $sku = $product->getSku();
            if (null === $sku) {
                continue;
            }
            $current = $this->stockService->getValidStock($sku);
            if ($product->getQuantity() > $current) {
                $this->logger->warning('检查时发现库存不足', [
                    'current' => $current,
                    'request' => $product->getQuantity(),
                    'product' => $product,
                    'sku' => $product->getSku(),
                ]);
                throw new \RuntimeException('库存不足');
            }
        }
    }

    /**
     * 订单创建后，我们要锁定一部分库存，防止超卖
     */
    #[AsEventListener]
    public function onAfterOrderCreated(AfterOrderCreatedEvent $event): void
    {
        if (null === $this->stockService || null === $this->entityLockService) {
            return;
        }

        $lockEntities = $this->buildLockEntities($event->getContract());

        $this->entityLockService->lockEntities($lockEntities, function () use ($event): void {
            $logs = [];
            try {
                foreach ($event->getContract()->getProducts() as $product) {
                    // 锁定库存
                    $stockLog = new StockLog();
                    $stockLog->setType(StockChange::LOCK);
                    $stockLog->setQuantity($product->getQuantity());
                    $sku = $product->getSku();
                    if (null !== $sku) {
                        $stockLog->setSku($sku);
                    }
                    $stockLog->setRemark(strval($product));
                    $logs[] = $stockLog;
                }
                $this->stockService->batchProcess($logs);
            } catch (\Throwable $exception) {
                $event->setRollback(true);
                throw $exception;
            }
        });
    }

    /**
     * 订单支付成功的话，我们释放锁定库存，同时真实进行扣除
     */
    #[AsEventListener]
    public function onOrderPaid(OrderPaidEvent $event): void
    {
        if (null === $this->stockService || null === $this->entityLockService) {
            return;
        }

        $lockEntities = $this->buildLockEntities($event->getContract());

        $this->entityLockService->lockEntities($lockEntities, function () use ($event): void {
            foreach ($event->getContract()->getProducts() as $product) {
                // 释放库存
                $stockLog1 = new StockLog();
                $stockLog1->setType(StockChange::UNLOCK);
                $stockLog1->setQuantity($product->getQuantity());
                $sku = $product->getSku();
                if (null !== $sku) {
                    $stockLog1->setSku($sku);
                }
                $stockLog1->setRemark(strval($product));

                // 再扣除库存
                $stockLog2 = new StockLog();
                $stockLog2->setType(StockChange::DEDUCT);
                $stockLog2->setQuantity($product->getQuantity());
                $sku2 = $product->getSku();
                if (null !== $sku2) {
                    $stockLog2->setSku($sku2);
                }
                $stockLog2->setRemark(strval($product));

                $this->stockService->batchProcess([$stockLog1, $stockLog2]);
            }
        });
    }

    /**
     * 订单取消的话，我们需要释放库存
     */
    #[AsEventListener]
    public function releaseLockedStock(AfterOrderCancelEvent $event): void
    {
        $this->logger->info('开始处理释放库存', [
            'order_sn' => $event->getContract()->getSn(),
            'order_id' => $event->getContract()->getId(),
            'state' => $event->getContract()->getState(),
        ]);
        if (null === $this->stockService || null === $this->entityLockService) {
            $this->logger->info('开始处理释放库存，服务不存在', [
                'order_sn' => $event->getContract()->getSn(),
                'order_id' => $event->getContract()->getId(),
                'state' => $event->getContract()->getState(),
            ]);

            return;
        }

        $lockEntities = $this->buildLockEntities($event->getContract());

        $this->entityLockService->lockEntities($lockEntities, function () use ($event): void {
            $this->processStockRelease($event->getContract());
        });
    }

    /**
     * @return array<LockEntity>
     */
    private function buildLockEntities(Contract $contract): array
    {
        $lockEntities = [];

        // Contract总是实现了LockEntity接口
        $lockEntities[] = $contract;

        foreach ($contract->getProducts() as $product) {
            $sku = $product->getSku();
            if ($sku instanceof LockEntity) {
                $lockEntities[] = $sku;
            }
        }

        return $lockEntities;
    }

    private function processStockRelease(Contract $contract): void
    {
        if ($this->isUnpaidOrder($contract)) {
            $this->unlockStock($contract);
        } else {
            $this->returnStock($contract);
        }
    }

    private function isUnpaidOrder(Contract $contract): bool
    {
        return in_array($contract->getState(), [OrderState::INIT, OrderState::PAYING], true);
    }

    private function unlockStock(Contract $contract): void
    {
        foreach ($contract->getProducts() as $product) {
            $this->createAndProcessStockLog($product, StockChange::UNLOCK);
        }
    }

    private function returnStock(Contract $contract): void
    {
        foreach ($contract->getProducts() as $product) {
            $this->createAndProcessStockLog($product, StockChange::RETURN);
        }
    }

    private function createAndProcessStockLog(OrderProduct $product, StockChange $changeType): void
    {
        if (null === $this->stockService) {
            return;
        }

        $stockLog = new StockLog();
        $stockLog->setType($changeType);
        $stockLog->setQuantity($product->getQuantity());
        $sku = $product->getSku();
        if (null !== $sku) {
            $stockLog->setSku($sku);
        }
        $stockLog->setRemark(strval($product));
        $this->stockService->process($stockLog);
    }
}
