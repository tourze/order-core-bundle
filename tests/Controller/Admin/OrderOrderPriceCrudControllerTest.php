<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Controller\Admin;

use OrderCoreBundle\Controller\Admin\OrderOrderPriceCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OrderOrderPriceCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OrderOrderPriceCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): OrderOrderPriceCrudController
    {
        /** @var OrderOrderPriceCrudController */
        return self::getContainer()->get(OrderOrderPriceCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '订单合同' => ['订单合同'];
        yield '关联商品' => ['关联商品'];
        yield '价格名目' => ['价格名目'];
        yield '价格类型' => ['价格类型'];
        yield '金额' => ['金额'];
        yield '单价' => ['单价'];
        yield '税费' => ['税费'];
        yield '币种' => ['币种'];
        yield '是否已支付' => ['是否已支付'];
        yield '是否可退款' => ['是否可退款'];
        yield '是否已退款' => ['是否已退款'];
        yield '备注' => ['备注'];
        yield 'SKU价格' => ['SKU价格'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
        yield '创建人' => ['创建人'];
        yield '更新人' => ['更新人'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'product' => ['product'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'money' => ['money'];
        yield 'unitPrice' => ['unitPrice'];
        yield 'tax' => ['tax'];
        yield 'currency' => ['currency'];
        yield 'paid' => ['paid'];
        yield 'canRefund' => ['canRefund'];
        yield 'refund' => ['refund'];
        yield 'remark' => ['remark'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'product' => ['product'];
        yield 'name' => ['name'];
        yield 'type' => ['type'];
        yield 'money' => ['money'];
        yield 'unitPrice' => ['unitPrice'];
        yield 'tax' => ['tax'];
        yield 'currency' => ['currency'];
        yield 'paid' => ['paid'];
        yield 'canRefund' => ['canRefund'];
        yield 'refund' => ['refund'];
        yield 'remark' => ['remark'];
    }

    public function testIndex(): void
    {
        $client = self::createAuthenticatedClient();
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
