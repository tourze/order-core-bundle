<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Enum\OrderState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Contract::class)]
final class ContractTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Contract();
    }

    public function testCanBeInstantiated(): void
    {
        $contract = new Contract();

        $this->assertInstanceOf(Contract::class, $contract);
    }

    public function testCanSetAndGetSn(): void
    {
        $contract = new Contract();
        $sn = 'CONTRACT_123456';

        $contract->setSn($sn);

        $this->assertSame($sn, $contract->getSn());
    }

    public function testCanSetAndGetState(): void
    {
        $contract = new Contract();
        $state = OrderState::INIT;

        $contract->setState($state);

        $this->assertSame($state, $contract->getState());
    }

    public function testCanSetAndGetRemark(): void
    {
        $contract = new Contract();
        $remark = 'Test remark';

        $contract->setRemark($remark);

        $this->assertSame($remark, $contract->getRemark());
    }

    public function testCanSetAndGetCancelReason(): void
    {
        $contract = new Contract();
        $reason = 'User cancelled';

        $contract->setCancelReason($reason);

        $this->assertSame($reason, $contract->getCancelReason());
    }

    public function testToStringReturnsSnWhenSet(): void
    {
        $contract = new Contract();
        $sn = 'CONTRACT_789';
        $contract->setSn($sn);

        $this->assertSame($sn, (string) $contract);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    /** @return \Generator<string, array{string, mixed}> */
    public static function propertiesProvider(): \Generator
    {
        yield 'sn' => ['sn', 'CONTRACT_TEST_001'];
        yield 'type' => ['type', 'default'];
        yield 'outTradeNo' => ['outTradeNo', 'OUT_TRADE_123'];
        yield 'remark' => ['remark', '订单备注信息'];
        yield 'cancelReason' => ['cancelReason', '用户取消订单'];
        yield 'lockVersion' => ['lockVersion', 1];
    }
}
