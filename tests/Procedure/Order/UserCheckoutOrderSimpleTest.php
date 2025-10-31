<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\UserCheckoutOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

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
        $procedure->products = ['product1', 'product2'];

        $request = $this->createMock(JsonRpcRequest::class);
        $result = $procedure->generateFormattedLogText($request);

        $this->assertStringContainsString('用户下单：商品数量=2', $result);
    }
}
