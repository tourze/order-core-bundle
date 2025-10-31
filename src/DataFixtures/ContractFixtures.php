<?php

namespace OrderCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\DoctrineResolveTargetEntityBundle\Service\ResolveTargetEntityService;

/**
 * 订单合同数据填充
 * 创建测试用的订单合同数据
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class ContractFixtures extends Fixture implements FixtureGroupInterface
{
    public const CONTRACT_PENDING = 'contract-pending';
    public const CONTRACT_PAID = 'contract-paid';
    public const CONTRACT_SHIPPED = 'contract-shipped';
    public const CONTRACT_RECEIVED = 'contract-received';
    public const CONTRACT_CANCELED = 'contract-canceled';

    public function __construct(
        private readonly ResolveTargetEntityService $resolveTargetEntityService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 创建待支付订单
        $pendingContract = new Contract();
        $pendingContract->setSn('C' . date('Ymd') . '001');
        $pendingContract->setType('default');
        $pendingContract->setState(OrderState::INIT);
        $pendingContract->setOutTradeNo('OUT' . time() . '001');
        $pendingContract->setRemark('测试待支付订单');
        $pendingContract->setUser($this->createMockUser());
        $pendingContract->setAutoCancelTime(new \DateTimeImmutable('+30 minutes'));

        $manager->persist($pendingContract);

        // 创建已支付订单
        $paidContract = new Contract();
        $paidContract->setSn('C' . date('Ymd') . '002');
        $paidContract->setType('default');
        $paidContract->setState(OrderState::PAID);
        $paidContract->setOutTradeNo('OUT' . time() . '002');
        $paidContract->setRemark('测试已支付订单');
        $paidContract->setUser($this->createMockUser());
        // 供应商审核功能已废弃

        $manager->persist($paidContract);

        // 创建已发货订单
        $shippedContract = new Contract();
        $shippedContract->setSn('C' . date('Ymd') . '003');
        $shippedContract->setType('default');
        $shippedContract->setState(OrderState::SHIPPED);
        $shippedContract->setOutTradeNo('OUT' . time() . '003');
        $shippedContract->setRemark('测试已发货订单');
        $shippedContract->setUser($this->createMockUser());
        $shippedContract->setStartReceiveTime(new \DateTimeImmutable('-2 hours'));
        $shippedContract->setExpireReceiveTime(new \DateTimeImmutable('+7 days'));

        $manager->persist($shippedContract);

        // 创建已完成订单
        $receivedContract = new Contract();
        $receivedContract->setSn('C' . date('Ymd') . '004');
        $receivedContract->setType('default');
        $receivedContract->setState(OrderState::RECEIVED);
        $receivedContract->setOutTradeNo('OUT' . time() . '004');
        $receivedContract->setRemark('测试已完成订单');
        $receivedContract->setUser($this->createMockUser());
        $receivedContract->setFinishTime(new \DateTimeImmutable('-1 day'));

        $manager->persist($receivedContract);

        // 创建已取消订单
        $canceledContract = new Contract();
        $canceledContract->setSn('C' . date('Ymd') . '005');
        $canceledContract->setType('default');
        $canceledContract->setState(OrderState::CANCELED);
        $canceledContract->setOutTradeNo('OUT' . time() . '005');
        $canceledContract->setRemark('测试已取消订单');
        $canceledContract->setCancelReason('用户主动取消');
        $canceledContract->setCancelTime(new \DateTimeImmutable('-2 hours'));
        $canceledContract->setUser($this->createMockUser());

        $manager->persist($canceledContract);

        $manager->flush();

        // 添加引用供其他Fixture使用
        $this->addReference(self::CONTRACT_PENDING, $pendingContract);
        $this->addReference(self::CONTRACT_PAID, $paidContract);
        $this->addReference(self::CONTRACT_SHIPPED, $shippedContract);
        $this->addReference(self::CONTRACT_RECEIVED, $receivedContract);
        $this->addReference(self::CONTRACT_CANCELED, $canceledContract);
    }

    private function createMockUser(): ?UserInterface
    {
        /** @var class-string $userClass */
        $userClass = $this->resolveTargetEntityService->findEntityClass(UserInterface::class);
        $rand = rand(1, 9);

        if ($this->hasReference("user-{$rand}", $userClass)) {
            $user = $this->getReference("user-{$rand}", $userClass);
            if ($user instanceof UserInterface) {
                return $user;
            }
        }

        return null;
    }

    public static function getGroups(): array
    {
        return ['order', 'test'];
    }
}
