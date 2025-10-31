<?php

namespace OrderCoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\ContractRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Service\SkuService;

#[AsCommand(name: self::NAME, description: '同步sku真实销量')]
class SyncSkuSalesRealTotalCommand extends Command
{
    public const NAME = 'order:sync-sku-sales-real-total';

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly SkuService $skuService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $skuList = $this->skuService->getAllSkus();

        /** @var Sku $sku */
        foreach ($skuList as $sku) {
            // 查询当前sku的销量
            $total = $this->contractRepository->createQueryBuilder('c')
                ->select('sum(p.quantity) as quantity')
                ->leftJoin('c.products', 'p')
                ->leftJoin('p.sku', 'sku')
                ->where('sku.id = :skuId and c.state in (:state)')
                ->setParameter('skuId', $sku->getId())
                ->setParameter('state', [
                    OrderState::RECEIVED,
                    OrderState::PAID,
                    OrderState::SHIPPED,
                    OrderState::PART_SHIPPED,
                ])
                ->getQuery()
                ->getSingleScalarResult()
            ;
            $totalInt = (int) ($total ?? 0);
            if ($totalInt <= $sku->getSalesReal()) {
                continue;
            }
            $sku->setSalesReal($totalInt);
            $this->entityManager->persist($sku);
            $this->entityManager->flush();
        }

        $output->writeln('任务结束');

        return Command::SUCCESS;
    }
}
