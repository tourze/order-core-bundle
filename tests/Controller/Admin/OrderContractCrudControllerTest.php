<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Controller\Admin;

use OrderCoreBundle\Controller\Admin\OrderContractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OrderContractCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OrderContractCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): OrderContractCrudController
    {
        /** @var OrderContractCrudController */
        return self::getContainer()->get(OrderContractCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'id' => ['ID'];
        yield 'sn' => ['订单编号'];
        yield 'type' => ['订单类型'];
        yield 'user' => ['用户'];
        yield 'state' => ['状态'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        // 只包含在form中显示的字段 (没有hideOnForm()的字段)
        yield 'sn' => ['sn'];
        yield 'type' => ['type'];
        yield 'user' => ['user'];
        yield 'state' => ['state'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        // 只包含在form中显示的字段 (没有hideOnForm()的字段)
        yield 'sn' => ['sn'];
        yield 'type' => ['type'];
        yield 'user' => ['user'];
        yield 'state' => ['state'];
    }

    public function testIndex(): void
    {
        $client = self::createAuthenticatedClient();
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testIndexWithoutAuthentication(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin');
    }
}
