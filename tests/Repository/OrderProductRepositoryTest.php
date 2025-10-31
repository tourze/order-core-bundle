<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderPrice;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Enum\OrderType;
use OrderCoreBundle\Repository\OrderProductRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(OrderProductRepository::class)]
#[RunTestsInSeparateProcesses]
final class OrderProductRepositoryTest extends AbstractRepositoryTestCase
{
    private OrderProductRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OrderProductRepository::class);
    }

    protected function createNewEntity(): OrderProduct
    {
        // 创建关联的Contract实体
        $contract = new Contract();
        $contract->setSn('TEST-CONTRACT-' . uniqid());
        $contract->setState(OrderState::PAID);
        $contract->setType(OrderType::DEFAULT->value);
        $contract->setRemark('Test Contract');

        // 添加价格信息
        $orderPrice = new OrderPrice();
        $orderPrice->setName('商品价格');
        $orderPrice->setCurrency('CNY');
        $orderPrice->setMoney('100.00');
        $orderPrice->setContract($contract);

        $contract->addPrice($orderPrice);

        self::getEntityManager()->persist($contract);
        self::getEntityManager()->persist($orderPrice);
        self::getEntityManager()->flush();

        // 创建OrderProduct实体
        $entity = new OrderProduct();
        $entity->setContract($contract);
        $entity->setValid(true);
        $entity->setQuantity(1);
        $entity->setRemark('Test product remark');
        $entity->setSource('test');
        $entity->setSkuUnit('piece');
        $entity->setAudited(true);

        return $entity;
    }

    /** @return ServiceEntityRepository<OrderProduct> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    public function testClear(): void
    {
        $entity = $this->createNewEntity();
        $this->repository->save($entity, false);

        $this->repository->clear();

        // 验证实体管理器已清空
        $this->assertFalse(self::getEntityManager()->contains($entity));
    }

    public function testFlush(): void
    {
        $entity = $this->createNewEntity();
        self::getEntityManager()->persist($entity);

        $this->repository->flush();

        // 验证实体已保存到数据库
        $this->assertGreaterThan(0, $entity->getId(), 'Entity should have a positive ID after persistence');
    }

    public function testSaveAll(): void
    {
        $entity1 = $this->createNewEntity();
        $entity2 = $this->createNewEntity();
        $entities = [$entity1, $entity2];

        $this->repository->saveAll($entities);

        // 验证所有实体都已保存
        $this->assertGreaterThan(0, $entity1->getId(), 'First entity should have a positive ID');
        $this->assertGreaterThan(0, $entity2->getId(), 'Second entity should have a positive ID');
    }
}
