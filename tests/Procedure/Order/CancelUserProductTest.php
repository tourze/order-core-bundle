<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Procedure\Order\CancelUserProduct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;
use Tourze\StockManageBundle\Entity\StockBatch;

/**
 * @internal
 */
#[CoversClass(CancelUserProduct::class)]
#[RunTestsInSeparateProcesses]
class CancelUserProductTest extends AbstractProcedureTestCase
{
    private CancelUserProduct $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(CancelUserProduct::class);

        // Mock StockService to avoid "未找到可退回库存的批次" error
        $this->mockStockService();
    }

    /**
     * 创建库存数据避免库存批次问题
     * 根据Linus的"数据结构至上"原则：创建完整的测试数据结构
     */
    private function mockStockService(): void
    {
        // 不Mock服务，而是创建必要的库存测试数据
        // 库存系统需要有对应的批次记录才能正常工作
    }

    /**
     * 创建测试用的Contract实体
     */
    private function createTestContract(UserInterface $user, OrderState $state = OrderState::INIT): Contract
    {
        $contract = new Contract();
        $contract->setSn('TEST-' . uniqid());
        $contract->setType('default');
        $contract->setState($state);
        $contract->setOutTradeNo('OUT-TEST-' . uniqid());
        $contract->setRemark('测试订单');
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
        // SPU 只需要设置必须的字段，大部分字段都有默认值

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
        $sku->setUnit('个'); // 设置必需的单位字段

        /** @var Sku */
        return $this->persistAndFlush($sku);
    }

    /**
     * 创建测试用的OrderProduct实体
     */
    private function createTestOrderProduct(Contract $contract): OrderProduct
    {
        // 创建测试用的 SKU
        $sku = $this->createTestSku();

        // 为SKU创建库存批次，避免"未找到可退回库存的批次"错误
        $this->createStockBatch($sku, 10);

        $orderProduct = new OrderProduct();
        $orderProduct->setContract($contract);
        $orderProduct->setSku($sku);
        $orderProduct->setQuantity(1);
        $orderProduct->setValid(true);

        /** @var OrderProduct */
        return $this->persistAndFlush($orderProduct);
    }

    /**
     * 为SKU创建库存批次
     */
    private function createStockBatch(Sku $sku, int $quantity): StockBatch
    {
        $stockBatch = new StockBatch();
        $stockBatch->setSku($sku);
        $stockBatch->setBatchNo('BATCH-' . uniqid());
        $stockBatch->setQuantity($quantity);
        $stockBatch->setAvailableQuantity($quantity);
        $stockBatch->setReservedQuantity(0);
        $stockBatch->setLockedQuantity(0);
        $stockBatch->setUnitCost(10.00); // 设置单位成本
        $stockBatch->setQualityLevel('A'); // 设置质量等级
        $stockBatch->setStatus('available'); // 设置状态为可用

        /** @var StockBatch */
        return $this->persistAndFlush($stockBatch);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CancelUserProduct::class, $this->procedure);
    }

    public function testInvokeCallsExecute(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser1', 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);
        $orderProduct = $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $this->procedure->contractId = (string) $contract->getId();
        $this->procedure->productId = (string) $orderProduct->getId();
        $this->procedure->cancelReason = 'test reason';
        $request = $this->createMock(JsonRpcRequest::class);

        $result = $this->procedure->__invoke($request);

        // 验证结果结构
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('取消成功', $result['__message']);
    }

    public function testExecuteSuccessfullyCancelsProductWithReason(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser2', 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);
        $orderProduct = $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $this->procedure->contractId = (string) $contract->getId();
        $this->procedure->productId = (string) $orderProduct->getId();
        $this->procedure->cancelReason = 'customer request';
        $result = $this->procedure->execute();

        // 验证结果结构
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('取消成功', $result['__message']);
    }

    public function testExecuteSuccessfullyCancelsProductWithoutReason(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser3', 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);
        $orderProduct = $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $this->procedure->contractId = (string) $contract->getId();
        $this->procedure->productId = (string) $orderProduct->getId();
        $this->procedure->cancelReason = null;
        $result = $this->procedure->execute();

        // 验证结果结构
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('取消成功', $result['__message']);
    }

    public function testExecuteThrowsExceptionWhenContractNotFound(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('testuser4', 'password');
        $this->setAuthenticatedUser($user);

        // 使用不存在的订单ID
        $this->procedure->contractId = '99999';
        $this->procedure->productId = '1';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到订单');

        $this->procedure->execute();
    }

    public function testExecuteThrowsExceptionWhenProductNotFound(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser5', 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用存在的订单但不存在的产品ID
        $this->procedure->contractId = (string) $contract->getId();
        $this->procedure->productId = '99999';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到产品信息');

        $this->procedure->execute();
    }

    public function testExecuteWithEntityLockCallback(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser6', 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);
        $orderProduct = $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务测试实体锁机制
        $this->procedure->contractId = (string) $contract->getId();
        $this->procedure->productId = (string) $orderProduct->getId();
        $result = $this->procedure->execute();

        // 验证执行成功
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('取消成功', $result['__message']);
    }
}
