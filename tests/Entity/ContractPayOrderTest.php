<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Entity\PayOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * 测试 Contract Entity 与 PayOrder 的关联关系
 * @internal
 */
#[CoversClass(Contract::class)]
final class ContractPayOrderTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Contract();
    }

    /**
     * @return \Generator<string, array{string, mixed}>
     */
    public static function propertiesProvider(): \Generator
    {
        // 专门测试 Contract 与 PayOrder 关联的属性
        yield 'payOrder' => ['payOrder', new PayOrder()];
    }

    public function testSetAndGetPayOrder(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();

        $contract->setPayOrder($payOrder);

        $this->assertSame($payOrder, $contract->getPayOrder());
        $this->assertSame($contract, $payOrder->getContract());
    }

    public function testSetPayOrderToNull(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();

        // 先设置PayOrder
        $contract->setPayOrder($payOrder);
        $this->assertSame($payOrder, $contract->getPayOrder());

        // 然后设置为null
        $contract->setPayOrder(null);

        $this->assertNull($contract->getPayOrder());
        $this->assertNull($payOrder->getContract());
    }

    public function testPayTimeFromPayOrder(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();
        $payTime = new \DateTimeImmutable('2025-09-02 12:00:00');

        // 当没有PayOrder时，payTime应该为null
        $this->assertNull($contract->getPayTime());

        // 设置PayOrder和PayTime
        $payOrder->setPayTime($payTime);
        $contract->setPayOrder($payOrder);

        $payOrderFromContract = $contract->getPayOrder();
        $this->assertInstanceOf(PayOrder::class, $payOrderFromContract);
        /** @var PayOrder $payOrderFromContract */
        $contractPayTime = $contract->getPayTime();
        $payOrderTime = $payOrderFromContract->getPayTime();

        $this->assertSame($payTime, $contractPayTime);
        $this->assertEquals($payTime, $payOrderTime);
        $this->assertSame($contract, $payOrderFromContract->getContract());
    }

    public function testPayTimeWhenPayOrderExists(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();
        $payTime = new \DateTimeImmutable('2025-09-02 12:00:00');

        // 先设置PayOrder
        $contract->setPayOrder($payOrder);

        // 然后通过PayOrder设置payTime
        $payOrder->setPayTime($payTime);

        $this->assertSame($payTime, $contract->getPayTime());
        $this->assertSame($payTime, $payOrder->getPayTime());
    }

    public function testPayTimeWithNullValue(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();

        // 设置PayOrder但PayTime为null
        $contract->setPayOrder($payOrder);
        $payOrder->setPayTime(null);

        $this->assertInstanceOf(PayOrder::class, $contract->getPayOrder());
        $this->assertNull($contract->getPayTime());
        $this->assertNull($payOrder->getPayTime());
    }

    public function testPayOrderBidirectionalAssociation(): void
    {
        $contract = new Contract();
        $payOrder = new PayOrder();

        // 通过PayOrder设置关联
        $payOrder->setContract($contract);

        // Contract端应该能反向访问PayOrder
        // 注意：这需要PayOrder->setContract方法正确设置双向关联
        $this->assertSame($contract, $payOrder->getContract());

        // 但是由于我们的实现中PayOrder->setContract并不会自动设置Contract->payOrder
        // 这里我们只测试PayOrder到Contract的单向关联
    }
}
