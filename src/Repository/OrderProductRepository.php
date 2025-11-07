<?php

namespace OrderCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OrderCoreBundle\Entity\OrderProduct;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<OrderProduct>
 */
#[AsRepository(entityClass: OrderProduct::class)]
#[Autoconfigure(public: true)]
class OrderProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProduct::class);
    }

    /**
     * 保存实体
     */
    public function save(OrderProduct $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除实体
     */
    public function remove(OrderProduct $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据订单和商品来源查找商品
     *
     * @param int $contractId 订单ID
     * @param string $source 商品来源 (normal, coupon_gift, coupon_redeem)
     * @return OrderProduct[]
     */
    public function findByContractAndSource(int $contractId, string $source): array
    {
        return $this->createQueryBuilder('op')
            ->andWhere('op.contract = :contractId')
            ->andWhere('op.source = :source')
            ->setParameter('contractId', $contractId)
            ->setParameter('source', $source)
            ->orderBy('op.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据订单分组查找商品（按类型分组）
     *
     * @param int $contractId 订单ID
     * @return array<string, OrderProduct[]>
     */
    public function findByContractGroupedBySource(int $contractId): array
    {
        $products = $this->createQueryBuilder('op')
            ->andWhere('op.contract = :contractId')
            ->setParameter('contractId', $contractId)
            ->orderBy('op.id', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [
            'normal' => [],
            'coupon_gift' => [],
            'coupon_redeem' => [],
        ];

        foreach ($products as $product) {
            $source = $product->getSource() ?? 'normal';
            if (!isset($grouped[$source])) {
                $grouped[$source] = [];
            }
            $grouped[$source][] = $product;
        }

        return $grouped;
    }

    /**
     * 批量保存
     * @param array<OrderProduct> $entities
     */
    public function saveAll(array $entities, bool $flush = true): void
    {
        foreach ($entities as $entity) {
            $this->getEntityManager()->persist($entity);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 刷新实体管理器
     */
    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * 清空实体管理器
     */
    public function clear(): void
    {
        $this->getEntityManager()->clear();
    }
}
