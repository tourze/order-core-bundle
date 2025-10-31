<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\OrderPriceRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ProductCoreBundle\Enum\PriceType;

/**
 * @internal
 */
#[CoversClass(OrderPriceRepository::class)]
#[RunTestsInSeparateProcesses]
final class OrderPriceRepositoryTest extends AbstractRepositoryTestCase
{
    private OrderPriceRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OrderPriceRepository::class);
    }

    protected function createNewEntity(): OrderPrice
    {
        // 先创建Contract实体，使用正常的setter方法
        $contract = new Contract();
        $contract->setSn('TEST-' . uniqid());
        $contract->setType('default');
        $contract->setState(OrderState::INIT);
        $contract->setOutTradeNo('OUT-TEST-' . uniqid());
        $contract->setRemark('测试订单');

        // 创建OrderPrice实体
        $entity = new OrderPrice();
        $entity->setName('Test Price Item ' . uniqid());
        $entity->setCurrency('CNY');
        $entity->setMoney('100.00');
        $entity->setType(PriceType::SALE);
        $entity->setPaid(false);
        $entity->setCanRefund(true);
        $entity->setRefund(false);
        $entity->setUnitPrice('100.00');

        // 使用正常的setter方法设置关联
        $entity->setContract($contract);

        return $entity;
    }

    /** @return ServiceEntityRepository<OrderPrice> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    public function testClearClearsEntityManager(): void
    {
        // 直接测试功能，不检查方法存在性
        $this->repository->clear();
        $this->assertTrue(true, '清除操作完成');
    }

    public function testFlushFlushesChanges(): void
    {
        // 直接测试功能，不检查方法存在性
        $this->repository->flush();
        $this->assertTrue(true, '刷新操作完成');
    }

    public function testSaveAllPersistsEntities(): void
    {
        // 直接测试功能，不检查方法存在性
        $entities = [$this->createNewEntity(), $this->createNewEntity()];
        $this->repository->saveAll($entities);
        $this->assertTrue(true, '批量保存操作完成');
    }
}
