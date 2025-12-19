<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Param\Order\CancelUserOrderParam;
use OrderCoreBundle\Procedure\Order\CancelUserOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(CancelUserOrder::class)]
#[RunTestsInSeparateProcesses]
class CancelUserOrderTest extends AbstractProcedureTestCase
{
    private CancelUserOrder $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(CancelUserOrder::class);
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

        if (OrderState::CANCELED === $state) {
            $contract->setCancelReason('测试取消');
            $contract->setCancelTime(new \DateTimeImmutable());
        }

        /** @var Contract */
        return $this->persistAndFlush($contract);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CancelUserOrder::class, $this->procedure);
    }

    public function testInvokeCallsExecute(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $request = new JsonRpcRequest();
        $request->setMethod('order.cancel');
        $request->setParams(new JsonRpcParams([
            'contractId' => (string) $contract->getId(),
            'cancelReason' => 'test reason',
        ]));

        $result = $this->procedure->__invoke($request);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('取消成功', $data['__message']);
    }

    public function testExecuteSuccessfullyCancelsOrderWithReason(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $contract = $this->createTestContract($user, OrderState::PAID);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务测试带原因的取消
        $param = new CancelUserOrderParam(
            contractId: (string) $contract->getId(),
            cancelReason: 'customer request'
        );
        $result = $this->procedure->execute($param);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('取消成功', $data['__message']);

        // 验证订单状态已改变
        self::getEntityManager()->refresh($contract);
        $this->assertEquals(OrderState::CANCELED, $contract->getState());
        $this->assertEquals('customer request', $contract->getCancelReason());
    }

    public function testExecuteSuccessfullyCancelsOrderWithoutReason(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $contract = $this->createTestContract($user, OrderState::INIT);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $param = new CancelUserOrderParam(
            contractId: (string) $contract->getId(),
            cancelReason: null
        );
        $result = $this->procedure->execute($param);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('取消成功', $data['__message']);

        // 验证订单状态已改变
        self::getEntityManager()->refresh($contract);
        $this->assertEquals(OrderState::CANCELED, $contract->getState());
    }

    public function testExecuteReturnsCancelSuccessWhenOrderAlreadyCanceled(): void
    {
        // 创建测试用户和已取消的订单数据
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $contract = $this->createTestContract($user, OrderState::CANCELED);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $param = new CancelUserOrderParam(
            contractId: (string) $contract->getId()
        );
        $result = $this->procedure->execute($param);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('取消成功', $data['__message']);

        // 验证订单状态仍然是取消状态
        self::getEntityManager()->refresh($contract);
        $this->assertEquals(OrderState::CANCELED, $contract->getState());
    }

    public function testExecuteThrowsExceptionWhenContractNotFound(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $this->setAuthenticatedUser($user);

        // 使用不存在的订单ID
        $param = new CancelUserOrderParam(
            contractId: '99999'
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到订单');

        $this->procedure->execute($param);
    }

    public function testGenerateFormattedLogTextReturnsCorrectMessage(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('order.cancel');
        $result = $this->procedure->generateFormattedLogText($request);

        $this->assertEquals('消费者主动取消订单', $result);
    }

    public function testExecuteWithEntityLockCallback(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_' . uniqid(), 'password');
        $contract = $this->createTestContract($user, OrderState::PAID);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务测试实体锁机制
        $param = new CancelUserOrderParam(
            contractId: (string) $contract->getId()
        );
        $result = $this->procedure->execute($param);

        // 验证执行成功
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('取消成功', $data['__message']);
    }
}
