<?php

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Service\NullDeliverOperation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(NullDeliverOperation::class)]
#[RunTestsInSeparateProcesses]
class NullDeliverOperationTest extends AbstractIntegrationTestCase
{
    private NullDeliverOperation $operation;

    protected function onSetUp(): void
    {
        $this->operation = self::getService(NullDeliverOperation::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(NullDeliverOperation::class, $this->operation);
    }

    public function testNotifyShipmentReturnsFalse(): void
    {
        $contract = $this->createMockContract(123, 'ORDER-2024-001');

        $result = $this->operation->notifyShipment($contract);

        $this->assertFalse($result);
    }

    public function testNotifyShipmentWithRealContract(): void
    {
        // 使用Mock对象，因为Contract没有setId方法
        $contract = $this->createMockContract(123, 'ORDER-2024-002');

        $result = $this->operation->notifyShipment($contract);

        $this->assertFalse($result, 'NullDeliverOperation应该总是返回false');
    }

    public function testMarkAllDeliveryAsReceivedReturnsFalse(): void
    {
        $contract = $this->createMockContract(456, 'ORDER-2024-003');

        // 此方法在NullDeliverOperation中不存在，使用存在的方法
        $result = $this->operation->markAllDeliveryAsReceived($contract, $this->createNormalUser(), new \DateTime());

        $this->assertFalse($result);
    }

    public function testHasDeliveryRecordsReturnsFalse(): void
    {
        $contract = $this->createMockContract(789, 'ORDER-2024-004');

        // 此方法在NullDeliverOperation中不存在，使用存在的方法
        $result = $this->operation->hasDeliveryRecords($contract);

        $this->assertFalse($result);
    }

    /**
     * 创建模拟的合约对象
     */
    private function createMockContract(int $id, string $sn): Contract
    {
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn($id);
        $contract->method('getSn')->willReturn($sn);

        return $contract;
    }
}
