<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Procedure\Order\GetOrderTrackLogs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;

/**
 * @internal
 */
#[CoversClass(GetOrderTrackLogs::class)]
#[RunTestsInSeparateProcesses]
final class GetOrderTrackLogsTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 该测试类不需要额外的设置
    }

    /**
     * 创建测试用的Contract实体
     */
    private function createTestContract(UserInterface $user, OrderState $state = OrderState::PAID): Contract
    {
        $contract = new Contract();
        $contract->setSn('TEST-' . uniqid());
        $contract->setType('default');
        $contract->setState($state);
        $contract->setUser($user);

        /** @var Contract */
        return $this->persistAndFlush($contract);
    }

    /**
     * 创建测试用的SPU实体
     */
    private function createTestSpu(): Spu
    {
        $spu = new Spu();
        $spu->setTitle('Test Product');

        /** @var Spu */
        return $this->persistAndFlush($spu);
    }

    /**
     * 创建测试用的SKU实体
     */
    private function createTestSku(): Sku
    {
        $spu = $this->createTestSpu();

        $sku = new Sku();
        $sku->setSpu($spu);
        $sku->setUnit('个');

        /** @var Sku */
        return $this->persistAndFlush($sku);
    }

    /**
     * 创建测试用的OrderProduct实体
     */
    private function createTestOrderProduct(Contract $contract): OrderProduct
    {
        $sku = $this->createTestSku();

        $orderProduct = new OrderProduct();
        $orderProduct->setContract($contract);
        $orderProduct->setSku($sku);
        $orderProduct->setQuantity(1);
        $orderProduct->setValid(true);

        /** @var OrderProduct */
        return $this->persistAndFlush($orderProduct);
    }

    public function testCanBeInstantiated(): void
    {
        $procedure = self::getService(GetOrderTrackLogs::class);
        $this->assertInstanceOf(GetOrderTrackLogs::class, $procedure);
    }

    public function testExecuteReturnsOrderTrackLogs(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser', 'password');
        $contract = $this->createTestContract($user);
        $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        $procedure = self::getService(GetOrderTrackLogs::class);

        // 使用真实的订单ID
        $procedure->orderId = $contract->getSn();

        $result = $procedure->execute();

        $this->assertIsArray($result);
        // 验证返回的是物流追踪记录数组
        foreach ($result as $trackLog) {
            $this->assertIsArray($trackLog, '每条物流记录应该是数组格式');
        }
    }
}
