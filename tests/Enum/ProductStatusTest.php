<?php

namespace OrderCoreBundle\Tests\Enum;

use OrderCoreBundle\Enum\ProductStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(ProductStatus::class)]
class ProductStatusTest extends AbstractEnumTestCase
{
    public function testToArrayMethod(): void
    {
        $result = ProductStatus::RECEIVED->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('received', $result['value']);
        $this->assertSame('已收到货', $result['label']);
    }

    public function testGenOptionsMethod(): void
    {
        $options = ProductStatus::genOptions();

        $this->assertIsArray($options);
        $this->assertCount(3, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertIsString($option['value']);
            $this->assertIsString($option['label']);
        }

        $values = array_column($options, 'value');
        $labels = array_column($options, 'label');
        $this->assertContains('received', $values);
        $this->assertContains('已收到货', $values);
        $this->assertContains('not_received', $values);
        $this->assertContains('已收到货', $labels);
        $this->assertContains('未收到货', $labels);
    }
}
