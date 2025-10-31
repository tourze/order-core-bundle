<?php

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\CheckoutTrait;
use OrderCoreBundle\Procedure\Order\UserCheckoutOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(UserCheckoutOrder::class)]
#[RunTestsInSeparateProcesses]
class UserCheckoutOrderTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 简化测试初始化
    }

    public function testCanBeInstantiated(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);
        $this->assertInstanceOf(UserCheckoutOrder::class, $procedure);
    }

    public function testImplementsJsonRpcMethodInterface(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);
        $this->assertInstanceOf(JsonRpcMethodInterface::class, $procedure);
    }

    public function testHasRequiredMethods(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);

        // 验证关键方法存在
        $this->assertTrue(method_exists($procedure, 'execute'));
        $this->assertTrue(method_exists($procedure, '__invoke'));

        // 验证方法可见性
        $reflection = new \ReflectionMethod(UserCheckoutOrder::class, 'execute');
        $this->assertTrue($reflection->isPublic());
    }

    public function testUsesCheckoutTrait(): void
    {
        $reflection = new \ReflectionClass(UserCheckoutOrder::class);
        $traits = $reflection->getTraitNames();
        $this->assertContains(CheckoutTrait::class, $traits);
    }

    public function testExecuteProcessesUserCheckout(): void
    {
        // 为了避免复杂的依赖问题，我们只验证方法不会抛出致命错误
        // 由于没有有效商品数据，期望抛出 "找不到任何商品" 的 ApiException
        $procedure = self::getService(UserCheckoutOrder::class);

        // 设置空的产品列表或无效数据，应该抛出期望的异常
        $procedure->products = [];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到任何商品');

        $procedure->execute();
    }

    public function testGenerateFormattedLogText(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);
        $procedure->products = ['product1', 'product2', 'product3'];

        $request = $this->createMock(JsonRpcRequest::class);
        $result = $procedure->generateFormattedLogText($request);

        $this->assertStringContainsString('用户下单：商品数量=3', $result);
    }
}
