<?php

namespace OrderCoreBundle\Statistics;

use Carbon\CarbonImmutable;
use OrderCoreBundle\Repository\ContractRepository;
use StatisticsBundle\Metric\MetricProviderInterface;

/**
 * 订单数量指标提供者
 */
class OrderCountMetricProvider implements MetricProviderInterface
{
    public function __construct(private readonly ContractRepository $contractRepository)
    {
    }

    public function getMetricId(): string
    {
        return 'total_order_count';
    }

    public function getMetricName(): string
    {
        return '订单数量';
    }

    public function getMetricDescription(): string
    {
        return '当日订单总数';
    }

    public function getMetricUnit(): string
    {
        return '单';
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
        $startDate = $date->startOfDay();
        $endDate = $date->endOfDay();

        // 从数据库中获取指定日期范围内的订单数量
        return $this->contractRepository->countByCreateTimeDateRange($startDate, $endDate);
    }
}
