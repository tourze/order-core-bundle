<?php

namespace OrderCoreBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OrderCoreBundle\Service\CollectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CollectionService::class)]
#[RunTestsInSeparateProcesses]
class CollectionServiceTest extends AbstractIntegrationTestCase
{
    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getService(CollectionService::class);
        $this->assertInstanceOf(CollectionService::class, $service);
    }

    public function testAddToCollectionWithNewItem(): void
    {
        $service = self::getService(CollectionService::class);
        $collection = new ArrayCollection();
        $item = 'test-item';
        $setterCalled = false;

        $setter = function () use (&$setterCalled) {
            $setterCalled = true;
        };

        $service->addToCollection($collection, $item, $setter);

        $this->assertTrue($collection->contains($item));
        $this->assertTrue($setterCalled);
        $this->assertCount(1, $collection);
    }

    public function testAddToCollectionWithExistingItem(): void
    {
        $service = self::getService(CollectionService::class);
        /** @var ArrayCollection<int|string, string> $collection */
        $collection = new ArrayCollection(['existing-item']);
        $item = 'existing-item';
        $setterCalled = false;

        $setter = function () use (&$setterCalled) {
            $setterCalled = true;
        };

        $service->addToCollection($collection, $item, $setter);

        $this->assertTrue($collection->contains($item));
        $this->assertFalse($setterCalled);
        $this->assertCount(1, $collection);
    }

    public function testRemoveFromCollectionWithExistingItem(): void
    {
        $service = self::getService(CollectionService::class);
        $item = 'test-item';
        /** @var ArrayCollection<int|string, string> $collection */
        $collection = new ArrayCollection([$item]);
        $removerCalled = false;

        $remover = function () use (&$removerCalled) {
            $removerCalled = true;
        };

        $service->removeFromCollection($collection, $item, $remover);

        $this->assertFalse($collection->contains($item));
        $this->assertTrue($removerCalled);
        $this->assertCount(0, $collection);
    }

    public function testRemoveFromCollectionWithNonExistingItem(): void
    {
        $service = self::getService(CollectionService::class);
        /** @var ArrayCollection<int|string, string> $collection */
        $collection = new ArrayCollection(['other-item']);
        $item = 'test-item';
        $removerCalled = false;

        $remover = function () use (&$removerCalled) {
            $removerCalled = true;
        };

        $service->removeFromCollection($collection, $item, $remover);

        $this->assertFalse($collection->contains($item));
        $this->assertFalse($removerCalled);
        $this->assertCount(1, $collection);
    }

    public function testAddToCollectionWithCallableParameters(): void
    {
        $service = self::getService(CollectionService::class);
        $collection = new ArrayCollection();
        $item = new \stdClass();
        $item->id = 123;
        $setterValue = null;

        $setter = function ($passedItem) use (&$setterValue) {
            if ($passedItem instanceof \stdClass && property_exists($passedItem, 'id')) {
                $setterValue = $passedItem->id;
            }
        };

        $service->addToCollection($collection, $item, $setter);

        $this->assertTrue($collection->contains($item));
        $this->assertEquals(123, $setterValue);
    }

    public function testRemoveFromCollectionWithCallableParameters(): void
    {
        $service = self::getService(CollectionService::class);
        $item = new \stdClass();
        $item->id = 456;
        /** @var ArrayCollection<int|string, \stdClass> $collection */
        $collection = new ArrayCollection([$item]);
        $removerValue = null;

        $remover = function ($passedItem) use (&$removerValue) {
            if ($passedItem instanceof \stdClass && property_exists($passedItem, 'id')) {
                $removerValue = $passedItem->id;
            }
        };

        $service->removeFromCollection($collection, $item, $remover);

        $this->assertFalse($collection->contains($item));
        $this->assertEquals(456, $removerValue);
    }

    protected function onSetUp(): void
    {
        // 无需特殊初始化
    }
}
