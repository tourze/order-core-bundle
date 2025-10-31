<?php

namespace OrderCoreBundle\Statistics;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OrderCoreBundle\Enum\OrderState;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * 收入金额指标提供者
 */
class RevenueMetricProvider implements MetricProviderInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function getMetricId(): string
    {
        return 'total_revenue';
    }

    public function getMetricName(): string
    {
        return '收入金额';
    }

    public function getMetricDescription(): string
    {
        return '当日总收入金额';
    }

    public function getMetricUnit(): string
    {
        return '元';
    }

    public function getCategory(): string
    {
        return '平台订单';
    }

    public function getCategoryOrder(): int
    {
        return 20;
    }

    public function getMetricValue(CarbonImmutable $date): int
    {
        // 设置日期范围为当天的开始和结束
        $startDate = $date->startOfDay()->format('Y-m-d H:i:s');
        $endDate = $date->endOfDay()->format('Y-m-d H:i:s');

        // 查询已支付订单的总收入金额
        $sql = <<<'SQL'
            SELECT COALESCE(SUM(CAST(op.money AS DECIMAL(20,2))), 0) as total_revenue
            FROM order_contract_price op
            JOIN order_contract_order o ON op.contract_id = o.id
            WHERE o.create_time BETWEEN :startDate AND :endDate
            AND o.state IN (:paidStates)
            AND op.currency = 'CNY'
            AND op.paid = TRUE
            SQL;

        // 获取已支付状态的订单状态列表
        $paidStates = [
            OrderState::PAID->value,
            OrderState::PART_SHIPPED->value,
            OrderState::SHIPPED->value,
            OrderState::RECEIVED->value,
        ];

        $result = $this->connection->executeQuery(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paidStates' => $paidStates,
            ],
            [
                'paidStates' => ArrayParameterType::STRING,
            ]
        )->fetchAssociative();

        // 将结果转换为整数（单位：分）
        if (false === $result || !isset($result['total_revenue'])) {
            return 0;
        }

        $totalRevenue = $result['total_revenue'];
        if (!is_numeric($totalRevenue)) {
            return 0;
        }

        return (int) ((float) $totalRevenue * 100);
    }
}
