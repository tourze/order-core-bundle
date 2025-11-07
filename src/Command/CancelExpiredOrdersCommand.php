<?php

declare(strict_types=1);

namespace OrderCoreBundle\Command;

use Carbon\CarbonImmutable;
use Monolog\Attribute\WithMonologChannel;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Repository\ContractRepository;
use OrderCoreBundle\Service\ContractService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Tourze\UserServiceContracts\UserManagerInterface;

#[AsCronTask(expression: '*/1 * * * *')]
#[AsCommand(
    name: self::NAME,
    description: 'Cancel expired unpaid orders and release stock automatically',
)]
#[WithMonologChannel(channel: 'order_core')]
final class CancelExpiredOrdersCommand extends Command
{
    public const NAME = 'order:cancel-expired';

    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly ContractService $contractService,
        private readonly LoggerInterface $logger,
        private readonly UserManagerInterface $userManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run in dry-run mode without making any changes')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Number of orders to process in each batch', '100')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of orders to process', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->parseConfiguration($input);

        $this->displayHeader($io, $config);

        $totalExpired = $this->countExpiredOrders();
        if (0 === $totalExpired) {
            $io->success('No expired orders found.');

            return Command::SUCCESS;
        }

        $processCount = $this->calculateProcessCount($totalExpired, $config['limit']);
        $io->info(sprintf('Found %d expired orders', $totalExpired));

        if ($config['dryRun']) {
            return $this->handleDryRun($io, $processCount);
        }

        return $this->processOrders($io, $config, $processCount);
    }

    /**
     * @return array{dryRun: bool, batchSize: int, limit: int|null, force: bool}
     */
    private function parseConfiguration(InputInterface $input): array
    {
        $limitOption = $input->getOption('limit');
        $batchSizeOption = $input->getOption('batch-size');

        return [
            'dryRun' => (bool) $input->getOption('dry-run'),
            'batchSize' => is_numeric($batchSizeOption) ? (int) $batchSizeOption : 100,
            'limit' => is_numeric($limitOption) ? (int) $limitOption : null,
            'force' => true,
        ];
    }

    /**
     * @param array{dryRun: bool, batchSize: int, limit: int|null, force: bool} $config
     */
    private function displayHeader(SymfonyStyle $io, array $config): void
    {
        if ($config['dryRun']) {
            $io->note('Running in dry-run mode. No orders will be cancelled.');
        }

        $io->title('Cancelling Expired Orders');

        $now = CarbonImmutable::now();
        $io->info(sprintf('Current time: %s', $now->format('Y-m-d H:i:s')));
    }

    private function calculateProcessCount(int $totalExpired, ?int $limit): int
    {
        return null !== $limit ? min($totalExpired, $limit) : $totalExpired;
    }

    private function handleDryRun(SymfonyStyle $io, int $processCount): int
    {
        $this->showExpiredOrdersPreview($io, min($processCount, 10));
        $io->success(sprintf('Dry-run completed. Would have cancelled %d orders.', $processCount));

        return Command::SUCCESS;
    }

    /**
     * @param array{dryRun: bool, batchSize: int, limit: int|null, force: bool} $config
     */
    private function processOrders(SymfonyStyle $io, array $config, int $processCount): int
    {
        $processed = $this->processBatches($io, $config['batchSize'], $config['limit']);

        $io->success(sprintf('Successfully cancelled %d expired orders.', $processed['success']));

        if ($processed['failed'] > 0) {
            $io->warning(sprintf('%d orders failed to cancel. Check logs for details.', $processed['failed']));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function countExpiredOrders(): int
    {
        $now = CarbonImmutable::now();

        $count = $this->contractRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.state IN (:states)')
            ->andWhere('c.autoCancelTime IS NOT NULL')
            ->andWhere('c.autoCancelTime <= :now')
            ->setParameter('states', [OrderState::INIT, OrderState::PAYING])
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) $count;
    }

    private function showExpiredOrdersPreview(SymfonyStyle $io, int $limit): void
    {
        $orders = $this->findExpiredOrdersBatch(0, $limit);

        if ([] === $orders) {
            return;
        }

        $io->section('Preview of expired orders:');

        $rows = [];
        foreach ($orders as $order) {
            $rows[] = [
                $order->getSn(),
                $order->getState()->getLabel(),
                $order->getAutoCancelTime()?->format('Y-m-d H:i:s') ?? 'N/A',
                $order->getTotalAmount() ?? '0.00',
            ];
        }

        $io->table(['Order SN', 'State', 'Auto Cancel Time', 'Total Amount'], $rows);
    }

    /**
     * @return array<string, int>
     */
    private function processBatches(SymfonyStyle $io, int $batchSize, ?int $limit): array
    {
        $totalProcessed = 0;
        $successCount = 0;
        $failedCount = 0;
        $offset = 0;

        $progressBar = $io->createProgressBar($limit ?? $this->countExpiredOrders());
        $progressBar->start();

        while (true) {
            $currentBatchSize = $batchSize;
            if (null !== $limit && ($totalProcessed + $batchSize) > $limit) {
                $currentBatchSize = $limit - $totalProcessed;
            }

            if ($currentBatchSize <= 0) {
                break;
            }

            $orders = $this->findExpiredOrdersBatch($offset, $currentBatchSize);

            if ([] === $orders) {
                break;
            }

            $batchResults = $this->processBatch($orders);
            $successCount += $batchResults['success'];
            $failedCount += $batchResults['failed'];
            $totalProcessed += count($orders);

            $progressBar->advance(count($orders));

            // Clear entity manager to prevent memory issues
            $this->contractRepository->clear();

            if (null !== $limit && $totalProcessed >= $limit) {
                break;
            }

            // Use count instead of offset to handle concurrent modifications
            if (count($orders) < $currentBatchSize) {
                break;
            }
        }

        $progressBar->finish();
        $io->newLine(2);

        return [
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * @return array<Contract>
     */
    private function findExpiredOrdersBatch(int $offset, int $batchSize): array
    {
        $now = CarbonImmutable::now();

        /** @var array<Contract> */
        return $this->contractRepository->createQueryBuilder('c')
            ->where('c.state IN (:states)')
            ->andWhere('c.autoCancelTime IS NOT NULL')
            ->andWhere('c.autoCancelTime <= :now')
            ->setParameter('states', [OrderState::INIT, OrderState::PAYING])
            ->setParameter('now', $now)
            ->orderBy('c.autoCancelTime', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($batchSize)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<Contract> $orders
     * @return array<string, int>
     */
    private function processBatch(array $orders): array
    {
        $successCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                $this->cancelOrder($order);
                ++$successCount;

                $this->logger->info('Order cancelled successfully', [
                    'order_sn' => $order->getSn(),
                    'order_id' => $order->getId(),
                    'auto_cancel_time' => $order->getAutoCancelTime()?->format('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                ++$failedCount;

                $this->logger->error('Failed to cancel order', [
                    'order_sn' => $order->getSn(),
                    'order_id' => $order->getId(),
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }

    private function cancelOrder(Contract $order): void
    {
        $cancelReason = sprintf(
            '订单超时未支付自动取消 (超时时间: %s)',
            $order->getAutoCancelTime()?->format('Y-m-d H:i:s') ?? 'N/A'
        );

        // Create a system user for automated operations
        $systemUser = $this->userManager->createUser(
            userIdentifier: 'system',
            password: '',
            roles: ['ROLE_SYSTEM']
        );

        // Use ContractService to cancel the order, which will trigger events for stock release
        $this->contractService->cancelOrder($order, $systemUser, $cancelReason);
    }
}
