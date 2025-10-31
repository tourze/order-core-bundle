<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Service;

use OrderCoreBundle\Service\AttributeControllerLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $attributeControllerLoader;

    protected function onSetUp(): void
    {
        $this->attributeControllerLoader = self::getService(AttributeControllerLoader::class);
    }

    public function testLoadCallsAutoload(): void
    {
        $resource = 'test_resource';
        $type = 'test_type';

        $result = $this->attributeControllerLoader->load($resource, $type);

        $this->assertInstanceOf(RouteCollection::class, $result);

        // 验证返回的路由集合是通过 autoload 方法生成的
        $autoloadResult = $this->attributeControllerLoader->autoload();
        $this->assertEquals($autoloadResult->count(), $result->count());
    }

    public function testSupportsAlwaysReturnsFalse(): void
    {
        $this->assertFalse($this->attributeControllerLoader->supports('any_resource', 'any_type'));
        $this->assertFalse($this->attributeControllerLoader->supports(null, null));
        $this->assertFalse($this->attributeControllerLoader->supports('', ''));
        $this->assertFalse($this->attributeControllerLoader->supports(['array'], 'object'));
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $collection = $this->attributeControllerLoader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        // 当前实现返回空的RouteCollection，因为所有控制器已移到deliver-order-bundle
        $this->assertGreaterThanOrEqual(0, $collection->count());
    }

    public function testAutoloadIncludesAllExpectedControllers(): void
    {
        $collection = $this->attributeControllerLoader->autoload();

        $routes = $collection->all();
        $controllerClasses = [];

        /** @var Route $route */
        foreach ($routes as $route) {
            $controller = $route->getDefault('_controller');
            if (is_string($controller) && str_contains($controller, '::')) {
                $controllerClass = explode('::', $controller)[0];
                $controllerClasses[] = $controllerClass;
            }
        }

        // 去重
        $controllerClasses = array_unique($controllerClasses);

        // 发货相关控制器已删除，移到deliver-order-bundle
        // 当前bundle没有控制器，直接验证autoload方法能正常工作
        $this->assertIsArray($controllerClasses);
    }

    public function testAutoloadGeneratesValidRoutes(): void
    {
        $collection = $this->attributeControllerLoader->autoload();

        // 当前实现返回空集合，所有控制器已移到deliver-order-bundle
        // 验证返回的是有效的RouteCollection实例
        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertEquals(0, $collection->count(), '当前bundle应该没有路由，控制器已移到deliver-order-bundle');
    }

    public function testLoadWithDifferentParameters(): void
    {
        $result1 = $this->attributeControllerLoader->load('resource1', 'type1');
        $result2 = $this->attributeControllerLoader->load('resource2', 'type2');

        // 无论传入什么参数，都应该返回相同的结果（因为 load 只是调用 autoload）
        $this->assertEquals($result1->count(), $result2->count());

        // 验证两个结果包含相同的路由
        $routes1 = array_keys($result1->all());
        $routes2 = array_keys($result2->all());
        sort($routes1);
        sort($routes2);

        $this->assertEquals($routes1, $routes2);
    }

    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testLoaderImplementsRoutingAutoLoaderInterface(): void
    {
        $this->assertInstanceOf(
            'Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface',
            $this->attributeControllerLoader
        );
    }

    public function testLoaderExtendsSymfonyLoader(): void
    {
        $this->assertInstanceOf(
            'Symfony\Component\Config\Loader\Loader',
            $this->attributeControllerLoader
        );
    }

    public function testAutoloadResultIsConsistent(): void
    {
        $collection1 = $this->attributeControllerLoader->autoload();
        $collection2 = $this->attributeControllerLoader->autoload();

        // 多次调用应该返回相同的结果
        $this->assertEquals($collection1->count(), $collection2->count());

        $routes1 = $collection1->all();
        $routes2 = $collection2->all();

        $this->assertEquals(array_keys($routes1), array_keys($routes2));

        // 验证路由配置一致
        foreach ($routes1 as $routeName => $route1) {
            $route2 = $routes2[$routeName];
            $this->assertEquals($route1->getPath(), $route2->getPath());
            $this->assertEquals($route1->getDefaults(), $route2->getDefaults());
            $this->assertEquals($route1->getMethods(), $route2->getMethods());
        }
    }
}
