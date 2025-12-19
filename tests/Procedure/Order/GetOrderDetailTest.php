<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\OrderProduct;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Param\Order\GetOrderDetailParam;
use OrderCoreBundle\Procedure\Order\GetOrderDetail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;
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
        $request = new JsonRpcRequest();
        $request->setMethod('order.detail');
        $request->setParams(new JsonRpcParams(['orderId' => $contract->getSn()]));

        $result = $this->procedure->__invoke($request);

        // 验证结果结构
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $this->assertIsArray($result->data);
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
        $param = new GetOrderDetailParam(orderId: $contract->getSn());
        $result = $this->procedure->execute($param);
        $data = $result->data;

        // 验证结果结构
        $this->assertIsArray($data);

        // 验证与 GetUserOrderList 一致的返回结构
        $this->assertArrayHasKey('user', $data, '应包含用户信息');
        $this->assertArrayHasKey('products', $data, '应包含商品信息');
        $this->assertArrayHasKey('prices', $data, '应包含价格信息');
        $this->assertArrayHasKey('contacts', $data, '应包含联系人信息');
        $this->assertArrayHasKey('price', $data, '应包含总价信息');

        // 验证数组格式
        $this->assertIsArray($data['products'], '商品信息应为数组');
        $this->assertIsArray($data['prices'], '价格信息应为数组');
        $this->assertIsArray($data['contacts'], '联系人信息应为数组');
    }

    public function testExecuteThrowsExceptionWhenContractNotFound(): void
    {
        $param = new GetOrderDetailParam(orderId: 'nonexistent-order');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到订单');

        $this->procedure->execute($param);
    }

    public function testGenerateFormattedLogTextReturnsCorrectMessage(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('order.detail');
        $result = $this->procedure->generateFormattedLogText($request);

        $this->assertEquals('查看订单详情', $result);
    }
}
