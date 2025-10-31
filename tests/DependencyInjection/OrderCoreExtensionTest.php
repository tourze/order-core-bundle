<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\DependencyInjection;

use OrderCoreBundle\DependencyInjection\OrderCoreExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(OrderCoreExtension::class)]
final class OrderCoreExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        return $container;
    }

    public function testLoad(): void
    {
        $configs = [];

        $extension = new OrderCoreExtension();
        $container = $this->getContainer();
        $extension->load($configs, $container);

        // 验证容器中有注册的服务定义
        $this->assertGreaterThan(0, count($container->getDefinitions()));

        // 验证扩展加载成功
        $this->assertNotEmpty($container->getDefinitions());
    }

    public function testExtensionExists(): void
    {
        $extension = new OrderCoreExtension();
        $this->assertNotNull($extension);
        // 测试扩展创建和基本功能
        $this->assertInstanceOf(OrderCoreExtension::class, $extension);
    }
}
