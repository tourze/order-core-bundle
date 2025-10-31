<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Controller\Admin;

use OrderCoreBundle\Controller\Admin\OrderOrderLogCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OrderOrderLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OrderOrderLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): OrderOrderLogCrudController
    {
        /** @var OrderOrderLogCrudController */
        return self::getContainer()->get(OrderOrderLogCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '订单合同' => ['订单合同'];
        yield '当前状态' => ['当前状态'];
        yield '订单号' => ['订单号'];
        yield '操作动作' => ['操作动作'];
        yield '描述信息' => ['描述信息'];
        yield '创建时间' => ['创建时间'];
        yield '创建人' => ['创建人'];
        yield '创建IP' => ['创建IP'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'currentState' => ['currentState'];
        yield 'orderSn' => ['orderSn'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'currentState' => ['currentState'];
        yield 'orderSn' => ['orderSn'];
    }

    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
