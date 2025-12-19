<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\UserCheckoutOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(UserCheckoutOrder::class)]
#[RunTestsInSeparateProcesses]
class UserCheckoutOrderSimpleTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
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

    public function testExecute(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);
        $this->assertTrue(method_exists($procedure, 'execute'));
    }

    public function testGenerateFormattedLogText(): void
    {
        $procedure = self::getService(UserCheckoutOrder::class);

        $request = new JsonRpcRequest();
        $request->setMethod('order.checkout');
        $request->setParams(new JsonRpcParams(['products' => ['product1', 'product2']]));
        $result = $procedure->generateFormattedLogText($request);

        $this->assertStringContainsString('用户下单：商品数量=2', $result);
    }
}
