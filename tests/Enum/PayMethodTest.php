<?php

namespace OrderCoreBundle\Tests\Enum;

use OrderCoreBundle\Enum\PayMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(PayMethod::class)]
class PayMethodTest extends AbstractEnumTestCase
{
    public function testToArrayMethod(): void
    {
        $result = PayMethod::WEAPP->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('weapp', $result['value']);
        $this->assertSame('微信小程序', $result['label']);
    }

    public function testGenOptionsMethod(): void
    {
        $options = PayMethod::genOptions();

        $this->assertIsArray($options);
        $this->assertCount(4, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertIsString($option['value']);
            $this->assertIsString($option['label']);
        }

        $values = array_column($options, 'value');
        $this->assertContains('weapp', $values);
        $this->assertContains('cod', $values);
        $this->assertContains('proxy', $values);
        $this->assertContains('point', $values);
    }
}
