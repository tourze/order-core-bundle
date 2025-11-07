<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Entity\Contract;
use OrderCoreBundle\Service\ContractMapperService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ContractMapperService::class)]
#[RunTestsInSeparateProcesses]
final class ContractMapperServiceTest extends AbstractIntegrationTestCase
{
    private ContractMapperService $contractMapperService;

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(ContractMapperService::class, $this->contractMapperService);
    }

    public function testMapCheckoutArrayShouldReturnAllRequiredKeys(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(123);
        $contract->method('getContacts')->willReturn(new ArrayCollection());
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractMapperService->mapCheckoutArray($contract);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('currencyPrices', $result);
        $this->assertArrayHasKey('appendPrices', $result);
        $this->assertArrayHasKey('displayPrice', $result);
        $this->assertArrayHasKey('displayTaxPrice', $result);
        $this->assertArrayHasKey('freightPrices', $result);
        $this->assertArrayHasKey('payable', $result);
        $this->assertArrayHasKey('needConsignee', $result);
        $this->assertArrayHasKey('contacts', $result);
        $this->assertArrayHasKey('products', $result);

        $this->assertSame(123, $result['id']);
        $this->assertIsArray($result['contacts']);
        $this->assertIsArray($result['products']);
    }

    public function testMapCheckoutArrayWithEmptyCollectionsShouldHandleGracefully(): void
    {
        // Arrange
        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(456);
        $contract->method('getContacts')->willReturn(new ArrayCollection());
        $contract->method('getProducts')->willReturn(new ArrayCollection());

        // Act
        $result = $this->contractMapperService->mapCheckoutArray($contract);

        // Assert
        $this->assertSame([], $result['contacts']);
        $this->assertSame([], $result['products']);
    }

    public function testMapPlainArrayShouldReturnBasicContractData(): void
    {
        // Arrange
        $createTime = new \DateTimeImmutable('2024-01-15 10:30:00');
        $updateTime = new \DateTimeImmutable('2024-01-16 14:45:00');

        $contract = new Contract();
        $contract->setSn('CONTRACT-20240115-001');
        $contract->setRemark('Test contract remark');

        // Use reflection to set private timestamp properties
        $reflection = new \ReflectionClass($contract);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($contract, 456);

        $createTimeProperty = $reflection->getProperty('createTime');
        $createTimeProperty->setValue($contract, $createTime);

        $updateTimeProperty = $reflection->getProperty('updateTime');
        $updateTimeProperty->setValue($contract, $updateTime);

        // Act
        $result = $this->contractMapperService->mapPlainArray($contract);

        // Assert
        $expected = [
            'id' => 456,
            'sn' => 'CONTRACT-20240115-001',
            'remark' => 'Test contract remark',
            'supplier' => null,
            'supplierAcceptTime' => null,
            'supplierRejectTime' => null,
            'createTime' => '2024-01-15 10:30:00',
            'updateTime' => '2024-01-16 14:45:00',
            'createUser' => null,
            'updateUser' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMapPlainArrayWithNullDateTimesShouldHandleGracefully(): void
    {
        // Arrange
        $contract = new Contract();
        $contract->setSn('CONTRACT-NULL-DATES');
        $contract->setRemark(null);

        // Use reflection to set private properties
        $reflection = new \ReflectionClass($contract);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($contract, 789);

        // createTime and updateTime remain null by default

        // Act
        $result = $this->contractMapperService->mapPlainArray($contract);

        // Assert
        $this->assertSame(789, $result['id']);
        $this->assertSame('CONTRACT-NULL-DATES', $result['sn']);
        $this->assertNull($result['remark']);
        $this->assertNull($result['createTime']);
        $this->assertNull($result['updateTime']);
    }

    public function testMapPlainArrayWithEmptyStringShouldReturnSameValue(): void
    {
        // Arrange
        $contract = new Contract();
        $contract->setSn('');
        $contract->setRemark('');

        // Use reflection to set private properties
        $reflection = new \ReflectionClass($contract);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($contract, 100);

        // Act
        $result = $this->contractMapperService->mapPlainArray($contract);

        // Assert
        $this->assertSame('', $result['sn']);
        $this->assertSame('', $result['remark']);
    }

    protected function onSetUp(): void
    {
        $this->contractMapperService = self::getService(ContractMapperService::class);
    }
}
