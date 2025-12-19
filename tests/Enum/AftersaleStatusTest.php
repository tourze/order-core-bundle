<?php

namespace OrderCoreBundle\Tests\Enum;

use OrderCoreBundle\Enum\AftersaleStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(AftersaleStatus::class)]
class AftersaleStatusTest extends AbstractEnumTestCase
{
    public function testGetLabel(): void
    {
        $this->assertSame('正常', AftersaleStatus::NORMAL->getLabel());
        $this->assertSame('审核中', AftersaleStatus::UNDER_REVIEW->getLabel());
        $this->assertSame('已同意', AftersaleStatus::APPROVED->getLabel());
        $this->assertSame('已完成', AftersaleStatus::COMPLETED->getLabel());
    }

    public function testGetOptions(): void
    {
        $options = AftersaleStatus::getOptions();

        $this->assertIsArray($options);
        $this->assertCount(4, $options);
        $this->assertSame('正常', $options['normal']);
        $this->assertSame('审核中', $options['under_review']);
        $this->assertSame('已同意', $options['approved']);
        $this->assertSame('已完成', $options['completed']);
    }

    public function testFromString(): void
    {
        $this->assertSame(AftersaleStatus::NORMAL, AftersaleStatus::fromString('normal'));
        $this->assertSame(AftersaleStatus::UNDER_REVIEW, AftersaleStatus::fromString('under_review'));
        $this->assertSame(AftersaleStatus::APPROVED, AftersaleStatus::fromString('approved'));
        $this->assertSame(AftersaleStatus::COMPLETED, AftersaleStatus::fromString('completed'));
        $this->assertNull(AftersaleStatus::fromString('invalid'));
    }

    public function testCanApplyAftersale(): void
    {
        $this->assertTrue(AftersaleStatus::NORMAL->canApplyAftersale());
        $this->assertFalse(AftersaleStatus::UNDER_REVIEW->canApplyAftersale());
        $this->assertFalse(AftersaleStatus::APPROVED->canApplyAftersale());
        $this->assertFalse(AftersaleStatus::COMPLETED->canApplyAftersale());
    }

    public function testIsAftersaleInProgress(): void
    {
        $this->assertFalse(AftersaleStatus::NORMAL->isAftersaleInProgress());
        $this->assertTrue(AftersaleStatus::UNDER_REVIEW->isAftersaleInProgress());
        $this->assertTrue(AftersaleStatus::APPROVED->isAftersaleInProgress());
        $this->assertFalse(AftersaleStatus::COMPLETED->isAftersaleInProgress());
    }

    public function testIsAftersaleCompleted(): void
    {
        $this->assertFalse(AftersaleStatus::NORMAL->isAftersaleCompleted());
        $this->assertFalse(AftersaleStatus::UNDER_REVIEW->isAftersaleCompleted());
        $this->assertFalse(AftersaleStatus::APPROVED->isAftersaleCompleted());
        $this->assertTrue(AftersaleStatus::COMPLETED->isAftersaleCompleted());
    }

    public function testToArrayMethod(): void
    {
        $result = AftersaleStatus::NORMAL->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertSame('normal', $result['value']);
        $this->assertSame('正常', $result['label']);
    }

    public function testGenOptionsMethod(): void
    {
        $options = AftersaleStatus::genOptions();

        $this->assertIsArray($options);
        $this->assertCount(4, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
