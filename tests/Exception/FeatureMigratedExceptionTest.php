<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Exception;

use Generator;
use OrderCoreBundle\Exception\FeatureMigratedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(FeatureMigratedException::class)]
final class FeatureMigratedExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This test doesn't require any setup
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new FeatureMigratedException('测试功能', '新位置');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(FeatureMigratedException::class, $exception);
    }

    #[DataProvider('featureMigrationDataProvider')]
    public function testExceptionMessageFormat(string $feature, string $newLocation, string $expectedMessage): void
    {
        $exception = new FeatureMigratedException($feature, $newLocation);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    /**
     * @return \Generator<string, array{string, string, string}>
     */
    public static function featureMigrationDataProvider(): \Generator
    {
        yield 'basic migration message' => [
            '订单管理',
            'OrderService',
            '订单管理功能已迁移到OrderService，请使用新的服务',
        ];

        yield 'payment feature migration' => [
            '支付处理',
            'PaymentBundle',
            '支付处理功能已迁移到PaymentBundle，请使用新的服务',
        ];

        yield 'user management migration' => [
            '用户认证',
            'SecurityBundle\UserAuthenticator',
            '用户认证功能已迁移到SecurityBundle\UserAuthenticator，请使用新的服务',
        ];

        yield 'empty feature name' => [
            '',
            'SomeService',
            '功能已迁移到SomeService，请使用新的服务',
        ];

        yield 'empty new location' => [
            '某功能',
            '',
            '某功能功能已迁移到，请使用新的服务',
        ];

        yield 'special characters in names' => [
            'API v1.0',
            'API\v2\Controller',
            'API v1.0功能已迁移到API\v2\Controller，请使用新的服务',
        ];
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('原始异常');
        $exception = new FeatureMigratedException('测试功能', '新服务', $previous);

        $this->assertSame('测试功能功能已迁移到新服务，请使用新的服务', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionWithoutPreviousException(): void
    {
        $exception = new FeatureMigratedException('测试功能', '新服务');

        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(FeatureMigratedException::class);
        $this->expectExceptionMessage('库存管理功能已迁移到StockBundle，请使用新的服务');
        $this->expectExceptionCode(0);

        throw new FeatureMigratedException('库存管理', 'StockBundle');
    }

    public function testExceptionCanBeCaught(): void
    {
        try {
            throw new FeatureMigratedException('缓存服务', 'CacheBundle\RedisCache');
        } catch (FeatureMigratedException $e) {
            $this->assertSame('缓存服务功能已迁移到CacheBundle\RedisCache，请使用新的服务', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }
    }

    public function testExceptionCanBeCaughtAsRuntimeException(): void
    {
        $caught = false;

        try {
            throw new FeatureMigratedException('日志服务', 'LogBundle');
        } catch (\RuntimeException $e) {
            $caught = true;
            $this->assertInstanceOf(FeatureMigratedException::class, $e);
        }

        $this->assertTrue($caught, 'Exception should be caught as RuntimeException');
    }

    public function testExceptionStackTrace(): void
    {
        $exception = new FeatureMigratedException('文件上传', 'FileUploadBundle');

        $trace = $exception->getTrace();
        $this->assertIsArray($trace);
        $this->assertNotEmpty($trace);
        $this->assertArrayHasKey('function', $trace[0]);
    }

    public function testMultipleExceptionInstances(): void
    {
        $exception1 = new FeatureMigratedException('功能A', '服务A');
        $exception2 = new FeatureMigratedException('功能B', '服务B');

        $this->assertNotSame($exception1, $exception2);
        $this->assertSame('功能A功能已迁移到服务A，请使用新的服务', $exception1->getMessage());
        $this->assertSame('功能B功能已迁移到服务B，请使用新的服务', $exception2->getMessage());
    }

    public function testExceptionChaining(): void
    {
        $original = new \InvalidArgumentException('参数错误');
        $wrapped = new FeatureMigratedException('数据验证', 'ValidationService', $original);
        $final = new FeatureMigratedException('整体功能', 'NewBundle', $wrapped);

        $this->assertSame($wrapped, $final->getPrevious());
        $this->assertSame($original, $final->getPrevious()->getPrevious());
    }
}
