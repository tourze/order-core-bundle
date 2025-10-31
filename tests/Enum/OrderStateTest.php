<?php

namespace OrderCoreBundle\Tests\Enum;

use OrderCoreBundle\Enum\OrderState;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(OrderState::class)]
class OrderStateTest extends AbstractEnumTestCase
{
    public function testToArrayMethod(): void
    {
        $result = OrderState::INIT->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('init', $result['value']);
        $this->assertSame('已创建', $result['label']);
    }

    public function testGenOptionsMethod(): void
    {
        $options = OrderState::genOptions();

        $this->assertIsArray($options);
        $this->assertCount(15, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertIsString($option['value']);
            $this->assertIsString($option['label']);
        }

        $values = array_column($options, 'value');
        $this->assertContains('init', $values);
        $this->assertContains('paid', $values);
        $this->assertContains('shipped', $values);
    }
}
