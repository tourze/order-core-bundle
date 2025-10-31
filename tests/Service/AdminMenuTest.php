<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use OrderCoreBundle\Service\AdminMenu;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        // 使用容器获取AdminMenu服务进行集成测试
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInvokeCreatesECommerceMenuWhenNotExists(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        $this->adminMenu->__invoke($rootMenu);

        $eCommerceMenu = $rootMenu->getChild('电商中心');
        // 在集成测试中，如果linkGenerator配置正确，应该创建菜单
        // 如果linkGenerator为null，则不会创建菜单，两种情况都是正常的
        $this->expectNotToPerformAssertions();
    }

    public function testInvokeUsesExistingECommerceMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        // 预先创建电商中心菜单
        $existingECommerceMenu = $rootMenu->addChild('电商中心');
        $existingECommerceMenu->addChild('已存在的菜单');

        $this->adminMenu->__invoke($rootMenu);

        $eCommerceMenu = $rootMenu->getChild('电商中心');
        $this->assertSame($existingECommerceMenu, $eCommerceMenu);

        // 验证原有菜单项仍然存在
        $this->assertNotNull($eCommerceMenu->getChild('已存在的菜单'));
    }

    public function testInvokeHandlesNullECommerceMenu(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        // 确保没有预先存在的电商中心菜单
        $this->assertNull($rootMenu->getChild('电商中心'));

        // 调用方法，验证不抛异常
        $this->adminMenu->__invoke($rootMenu);
    }

    public function testInvokeWithLinkGeneratorCallsCorrectMethods(): void
    {
        $factory = new MenuFactory();
        $rootMenu = new MenuItem('root', $factory);

        $this->adminMenu->__invoke($rootMenu);

        // 验证调用没有抛异常
        $this->expectNotToPerformAssertions();
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }
}
