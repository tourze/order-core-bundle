<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Controller\Admin;

use OrderCoreBundle\Controller\Admin\OrderOrderContactCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(OrderOrderContactCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OrderOrderContactCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): OrderOrderContactCrudController
    {
        /** @var OrderOrderContactCrudController */
        return self::getContainer()->get(OrderOrderContactCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '订单' => ['订单'];
        yield '姓名' => ['姓名'];
        yield '手机号' => ['手机号'];
        yield '证件类型' => ['证件类型'];
        yield '是否激活' => ['是否激活'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'realname' => ['realname'];
        yield 'mobile' => ['mobile'];
        yield 'cardType' => ['cardType'];
        yield 'idCard' => ['idCard'];
        yield 'address' => ['address'];
        yield 'email' => ['email'];
        yield 'provinceName' => ['provinceName'];
        yield 'cityName' => ['cityName'];
        yield 'areaName' => ['areaName'];
        yield 'name' => ['name'];
        yield 'phone' => ['phone'];
        yield 'position' => ['position'];
        yield 'department' => ['department'];
        yield 'contactType' => ['contactType'];
        yield 'isActive' => ['isActive'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'contract' => ['contract'];
        yield 'realname' => ['realname'];
        yield 'mobile' => ['mobile'];
        yield 'cardType' => ['cardType'];
        yield 'idCard' => ['idCard'];
        yield 'address' => ['address'];
        yield 'email' => ['email'];
        yield 'provinceName' => ['provinceName'];
        yield 'cityName' => ['cityName'];
        yield 'areaName' => ['areaName'];
        yield 'name' => ['name'];
        yield 'phone' => ['phone'];
        yield 'position' => ['position'];
        yield 'department' => ['department'];
        yield 'contactType' => ['contactType'];
        yield 'isActive' => ['isActive'];
    }

    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
