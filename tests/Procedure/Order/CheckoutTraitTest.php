<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Procedure\Order;

use OrderCoreBundle\Procedure\Order\CheckoutTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutTrait::class)]
final class CheckoutTraitTest extends TestCase
{
    protected function onSetUp(): void
    {
        // 测试初始化逻辑（当前无需特殊设置）
    }

    public function testTraitExists(): void
    {
        $this->assertTrue(trait_exists(CheckoutTrait::class));
    }
}
