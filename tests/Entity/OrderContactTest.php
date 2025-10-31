<?php

declare(strict_types=1);

namespace OrderCoreBundle\Tests\Entity;

use Generator;
use OrderCoreBundle\Entity\OrderContact;
use OrderCoreBundle\Enum\CardType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(OrderContact::class)]
final class OrderContactTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OrderContact();
    }

    public function testCanBeInstantiated(): void
    {
        $entity = new OrderContact();
        $this->assertInstanceOf(OrderContact::class, $entity);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    /** @return \Generator<string, array{string, mixed}> */
    public static function propertiesProvider(): \Generator
    {
        yield 'realname' => ['realname', 'John Doe'];
        yield 'mobile' => ['mobile', '13800138000'];
        yield 'cardType' => ['cardType', CardType::ID_CARD];
        yield 'idCard' => ['idCard', '123456789012345678'];
        yield 'address' => ['address', '123 Test Street'];
        yield 'email' => ['email', 'test@example.com'];
        yield 'provinceName' => ['provinceName', 'Test Province'];
        yield 'cityName' => ['cityName', 'Test City'];
        yield 'areaName' => ['areaName', 'Test Area'];
        yield 'name' => ['name', 'John Doe'];
        yield 'phone' => ['phone', '13800138000'];
        yield 'position' => ['position', 'Manager'];
        yield 'department' => ['department', 'IT Department'];
        yield 'contactType' => ['contactType', 'primary'];
        yield 'isActive' => ['isActive', true];
    }
}
