<?php

namespace OrderCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use OrderCoreBundle\Entity\OrderLog;
use OrderCoreBundle\Enum\OrderState;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class OrderLogFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['order', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        $logData = [
            [
                'action' => 'order_created',
                'description' => '订单创建成功',
                'level' => 'info',
                'context' => ['order_id' => 1001, 'user_id' => 'user_001'],
                'ipAddress' => '192.168.1.100',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'createTime' => new \DateTimeImmutable('2025-01-20 10:30:00'),
                'currentState' => OrderState::INIT,
            ],
            [
                'action' => 'payment_completed',
                'description' => '订单支付完成',
                'level' => 'info',
                'context' => ['order_id' => 1001, 'payment_method' => 'wechat_pay', 'amount' => 299.00],
                'ipAddress' => '192.168.1.100',
                'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'createTime' => new \DateTimeImmutable('2025-01-20 10:35:00'),
                'currentState' => OrderState::PAID,
            ],
            [
                'action' => 'order_shipped',
                'description' => '订单已发货',
                'level' => 'info',
                'context' => ['order_id' => 1002, 'tracking_number' => 'SF202501200001'],
                'ipAddress' => '192.168.1.50',
                'userAgent' => 'Internal System Agent v1.0',
                'createTime' => new \DateTimeImmutable('2025-01-21 09:15:00'),
                'currentState' => OrderState::SHIPPED,
            ],
            [
                'action' => 'order_cancelled',
                'description' => '订单被取消',
                'level' => 'warning',
                'context' => ['order_id' => 1003, 'reason' => '用户主动取消', 'refund_amount' => 199.00],
                'ipAddress' => '192.168.1.101',
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'createTime' => new \DateTimeImmutable('2025-01-21 14:20:00'),
                'currentState' => OrderState::CANCELED,
            ],
            [
                'action' => 'order_delivery_failed',
                'description' => '订单配送失败',
                'level' => 'error',
                'context' => ['order_id' => 1004, 'reason' => '收货地址不详', 'retry_count' => 2],
                'ipAddress' => '192.168.1.60',
                'userAgent' => 'Delivery System v2.1',
                'createTime' => new \DateTimeImmutable('2025-01-22 16:45:00'),
                'currentState' => OrderState::EXCEPTION,
            ],
        ];

        foreach ($logData as $index => $data) {
            $log = new OrderLog();
            $log->setAction($data['action']);
            $log->setDescription($data['description']);
            $log->setLevel($data['level']);
            $log->setContext($data['context']);
            $log->setIpAddress($data['ipAddress']);
            $log->setUserAgent($data['userAgent']);
            $log->setCreateTime($data['createTime']);
            $log->setCurrentState($data['currentState']);

            $manager->persist($log);
            $this->addReference('order-log-' . $index, $log);
        }

        $manager->flush();
    }
}
