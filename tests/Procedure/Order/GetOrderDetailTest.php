<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Procedure\Order\GetOrderDetail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use Tourze\ProductCoreBundle\Entity\Sku;
use Tourze\ProductCoreBundle\Entity\Spu;

/**
 * @internal
 */
#[CoversClass(GetOrderDetail::class)]
#[RunTestsInSeparateProcesses]
class GetOrderDetailTest extends AbstractProcedureTestCase
{
    private GetOrderDetail $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetOrderDetail::class);
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
        $this->assertInstanceOf(GetOrderDetail::class, $this->procedure);
    }

    public function testInvokeCallsExecute(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser1', 'password');
        $contract = $this->createTestContract($user);
        $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实的订单ID进行测试
        $this->procedure->orderId = $contract->getSn();
        $request = $this->createMock(JsonRpcRequest::class);

        $result = $this->procedure->__invoke($request);

        // 验证结果结构
        $this->assertIsArray($result);
    }

    public function testExecuteReturnsOrderDetail(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser2', 'password');
        $contract = $this->createTestContract($user);
        $this->createTestOrderProduct($contract);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实的订单ID进行测试
        $this->procedure->orderId = $contract->getSn();
        $result = $this->procedure->execute();

        // 验证结果结构
        $this->assertIsArray($result);

        // 验证与 GetUserOrderList 一致的返回结构
        $this->assertArrayHasKey('user', $result, '应包含用户信息');
        $this->assertArrayHasKey('products', $result, '应包含商品信息');
        $this->assertArrayHasKey('prices', $result, '应包含价格信息');
        $this->assertArrayHasKey('contacts', $result, '应包含联系人信息');
        $this->assertArrayHasKey('price', $result, '应包含总价信息');

        // 验证数组格式
        $this->assertIsArray($result['products'], '商品信息应为数组');
        $this->assertIsArray($result['prices'], '价格信息应为数组');
        $this->assertIsArray($result['contacts'], '联系人信息应为数组');
    }

    public function testExecuteThrowsExceptionWhenContractNotFound(): void
    {
        $this->procedure->orderId = 'nonexistent-order';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到订单');

        $this->procedure->execute();
    }

    public function testGenerateFormattedLogTextReturnsCorrectMessage(): void
    {
        $request = $this->createMock(JsonRpcRequest::class);
        $result = $this->procedure->generateFormattedLogText($request);

        $this->assertEquals('查看订单详情', $result);
    }
}
