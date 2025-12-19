<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Param\Order;

use OrderCoreBundle\Param\Order\UserCheckoutOrderParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(UserCheckoutOrderParam::class)]
final class UserCheckoutOrderParamTest extends TestCase
{
    public function testImplementsRpcParamInterface(): void
    {
        $param = new UserCheckoutOrderParam(products: [['skuId' => '1', 'quantity' => 1]]);
        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }

    public function testConstructorWithAllParameters(): void
    {
        $products = [
            ['skuId' => 'sku-1', 'quantity' => 2],
            ['skuId' => 'sku-2', 'quantity' => 1],
        ];

        $param = new UserCheckoutOrderParam(
            products: $products,
            addressId: 'addr-123',
            remark: '请尽快发货'
        );

        $this->assertSame($products, $param->products);
        $this->assertSame('addr-123', $param->addressId);
        $this->assertSame('请尽快发货', $param->remark);
    }

    public function testConstructorWithRequiredParametersOnly(): void
    {
        $products = [['skuId' => 'sku-1', 'quantity' => 1]];
        $param = new UserCheckoutOrderParam(products: $products);

        $this->assertSame($products, $param->products);
        $this->assertNull($param->addressId);
        $this->assertNull($param->remark);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $param = new UserCheckoutOrderParam();

        $this->assertSame([], $param->products);
        $this->assertNull($param->addressId);
        $this->assertNull($param->remark);
    }

    public function testValidationPassesWithValidData(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new UserCheckoutOrderParam(
            products: [['skuId' => 'sku-1', 'quantity' => 1]]
        );

        $violations = $validator->validate($param);
        $this->assertCount(0, $violations);
    }

    public function testValidationFailsWithEmptyProducts(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $param = new UserCheckoutOrderParam(products: []);

        $violations = $validator->validate($param);
        $this->assertGreaterThan(0, count($violations));
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(UserCheckoutOrderParam::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
