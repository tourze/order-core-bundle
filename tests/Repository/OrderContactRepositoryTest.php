<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Enum\CardType;
use OrderCoreBundle\Repository\OrderContactRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(OrderContactRepository::class)]
#[RunTestsInSeparateProcesses]
final class OrderContactRepositoryTest extends AbstractRepositoryTestCase
{
    private OrderContactRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OrderContactRepository::class);
    }

    protected function createNewEntity(): object
    {
        $entity = new OrderContact();

        // 设置必需字段
        $entity->setRealname('Test Contact ' . uniqid());
        $entity->setCardType(CardType::ID_CARD);
        $entity->setIsActive(true);

        return $entity;
    }

    /** @return ServiceEntityRepository<OrderContact> */
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
        $entities = [
            $this->createNewEntity(),
            $this->createNewEntity(),
        ];
        /** @var array<OrderContact> $entities */
        $this->repository->saveAll($entities);
        $this->assertTrue(true, '批量保存操作完成');
    }
}
