<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use OrderCoreBundle\Param\Order\ReceiveUserOrderParam;
use OrderCoreBundle\Procedure\Order\ReceiveUserOrder;
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
#[CoversClass(ReceiveUserOrder::class)]
#[RunTestsInSeparateProcesses]
class ReceiveUserOrderTest extends AbstractProcedureTestCase
{
    private ReceiveUserOrder $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(ReceiveUserOrder::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ReceiveUserOrder::class, $this->procedure);
    }

    public function testInvokeCallsExecute(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_receive1', 'password');
        $contract = $this->createTestContract($user, OrderState::SHIPPED);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $request = new JsonRpcRequest();
        $request->setMethod('order.receive');
        $request->setParams(new JsonRpcParams([
            'contractId' => (string) $contract->getId(),
        ]));

        $result = $this->procedure->__invoke($request);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $data = $result->toArray();
        $this->assertArrayHasKey('__message', $data);
        $this->assertEquals('收货成功', $data['__message']);
    }

    public function testExecuteSuccessfullyReceivesOrder(): void
    {
        // 创建测试用户和订单数据
        $user = $this->createNormalUser('testuser_receive2', 'password');
        $contract = $this->createTestContract($user, OrderState::SHIPPED);

        // 设置认证用户
        $this->setAuthenticatedUser($user);

        // 使用真实服务进行集成测试
        $param = new ReceiveUserOrderParam(
            contractId: (string) $contract->getId(),
        );
        $result = $this->procedure->execute($param);

        // 验证结果结构
        $this->assertInstanceOf(ArrayResult::class, $result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('收货成功', $result['__message']);
    }

    public function testExecuteThrowsExceptionWhenContractNotFound(): void
    {
        $param = new ReceiveUserOrderParam(
            contractId: 'nonexistent-contract',
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到订单');

        $this->procedure->execute($param);
    }

    public function testGenerateFormattedLogTextReturnsCorrectMessage(): void
    {
        $request = new JsonRpcRequest();
        $request->setMethod('order.receive');
        $result = $this->procedure->generateFormattedLogText($request);

        $this->assertEquals('确认收货', $result);
    }

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
}
