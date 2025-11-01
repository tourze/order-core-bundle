<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Controller\Admin;

use OrderCoreBundle\Controller\Admin\OrderOrderProductCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OrderOrderProductCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OrderOrderProductCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): OrderOrderProductCrudController
    {
        /** @var OrderOrderProductCrudController */
        return self::getContainer()->get(OrderOrderProductCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '订单' => ['订单'];
        yield 'SPU' => ['SPU'];
        yield 'SKU' => ['SKU'];
        yield '数量' => ['数量'];
        yield '售价' => ['售价'];
        yield '币种' => ['币种'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'spu' => ['spu'];
        yield 'sku' => ['sku'];
        yield 'quantity' => ['quantity'];
        yield 'price' => ['price'];
        yield 'currency' => ['currency'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'spu' => ['spu'];
        yield 'sku' => ['sku'];
        yield 'quantity' => ['quantity'];
        yield 'price' => ['price'];
        yield 'currency' => ['currency'];
    }

    public function testIndex(): void
    {
        $client = self::createAuthenticatedClient();
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
