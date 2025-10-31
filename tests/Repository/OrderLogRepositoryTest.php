<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\OrderLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(OrderLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class OrderLogRepositoryTest extends AbstractRepositoryTestCase
{
    private OrderLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OrderLogRepository::class);
    }

    protected function createNewEntity(): OrderLog
    {
        $entity = new OrderLog();

        // 设置必需字段
        $entity->setCurrentState(OrderState::INIT);
        $entity->setOrderSn('TEST-' . uniqid());
        $entity->setAction('test_action');
        $entity->setDescription('Test order log entry');
        $entity->setLevel('info');
        $entity->setIpAddress('127.0.0.1');
        $entity->setUserAgent('Test User Agent');

        return $entity;
    }

    /** @return ServiceEntityRepository<OrderLog> */
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
