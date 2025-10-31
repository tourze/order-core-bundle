<?php

namespace OrderCoreBundle\Tests\DTO;

use OrderCoreBundle\DTO\DeliveryOrderDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DeliveryOrderDTO::class)]
class DeliveryOrderDTOTest extends TestCase
{
    private DeliveryOrderDTO $dto;

    protected function setUp(): void
    {
        $this->dto = new DeliveryOrderDTO();
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(DeliveryOrderDTO::class, $this->dto);
    }

    public function testSetAndGetId(): void
    {
        $id = 'test-id-123';

        $this->dto->setId($id);

        $this->assertSame($id, $this->dto->getId());
    }

    public function testIdDefaultsToNull(): void
    {
        $this->assertNull($this->dto->getId());
    }

    public function testSetAndGetExpressCompany(): void
    {
        $company = '顺丰速运';

        $this->dto->setExpressCompany($company);

        $this->assertSame($company, $this->dto->getExpressCompany());
    }

    public function testExpressCompanyDefaultsToNull(): void
    {
        $this->assertNull($this->dto->getExpressCompany());
    }

    public function testSetAndGetExpressNumber(): void
    {
        $number = 'SF1234567890';

        $this->dto->setExpressNumber($number);

        $this->assertSame($number, $this->dto->getExpressNumber());
    }

    public function testExpressNumberDefaultsToNull(): void
    {
        $this->assertNull($this->dto->getExpressNumber());
    }

    public function testSetters(): void
    {
        $this->dto->setId('123');
        $this->dto->setExpressCompany('中通快递');
        $this->dto->setExpressNumber('ZTO123456789');

        $this->assertSame('123', $this->dto->getId());
        $this->assertSame('中通快递', $this->dto->getExpressCompany());
        $this->assertSame('ZTO123456789', $this->dto->getExpressNumber());
    }
}
