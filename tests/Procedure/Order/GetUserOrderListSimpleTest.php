<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\GetUserOrderList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserOrderList::class)]
#[RunTestsInSeparateProcesses]
class GetUserOrderListSimpleTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 实现抽象方法 - 在这里可以进行测试特定的设置
    }

    public function testCanBeInstantiated(): void
    {
        $procedure = self::getService(GetUserOrderList::class);
        $this->assertInstanceOf(GetUserOrderList::class, $procedure);
    }

    public function testImplementsJsonRpcMethodInterface(): void
    {
        $procedure = self::getService(GetUserOrderList::class);
        $this->assertInstanceOf(JsonRpcMethodInterface::class, $procedure);
    }

    public function testHasInvokeMethod(): void
    {
        $procedure = self::getService(GetUserOrderList::class);
        $this->assertTrue(method_exists($procedure, '__invoke'));
    }

    public function testHasExecuteMethod(): void
    {
        $procedure = self::getService(GetUserOrderList::class);
        $this->assertTrue(method_exists($procedure, 'execute'));
    }

    public function testExecuteMethodExists(): void
    {
        $procedure = self::getService(GetUserOrderList::class);

        // 只测试方法存在性和基本结构，不执行实际业务逻辑
        $this->assertTrue(method_exists($procedure, 'execute'));

        // 验证方法是public的
        $reflection = new \ReflectionMethod($procedure, 'execute');
        $this->assertTrue($reflection->isPublic());
    }
}
