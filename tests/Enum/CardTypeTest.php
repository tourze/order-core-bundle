<?php

namespace OrderCoreBundle\Tests\Enum;

use OrderCoreBundle\Enum\CardType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(CardType::class)]
class CardTypeTest extends AbstractEnumTestCase
{
    public function testToArrayMethod(): void
    {
        $result = CardType::ID_CARD->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('id-card', $result['value']);
        $this->assertSame('身份证', $result['label']);
    }

    public function testGenOptionsMethod(): void
    {
        $options = CardType::genOptions();

        $this->assertIsArray($options);
        $this->assertCount(1, $options);

        $option = $options[0];
        $this->assertArrayHasKey('value', $option);
        $this->assertArrayHasKey('label', $option);
        $this->assertSame('id-card', $option['value']);
        $this->assertSame('身份证', $option['label']);
    }
}
