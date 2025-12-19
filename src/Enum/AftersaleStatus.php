<?php

declare(strict_types=1);

namespace OrderCoreBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 售后状态枚举
 */
enum AftersaleStatus: string implements Labelable, Itemable, Selectable
{

    use ItemTrait;
    use SelectTrait;

    case NORMAL = 'normal';           // 正常
    case UNDER_REVIEW = 'under_review'; // 审核中
    case APPROVED = 'approved';       // 已同意
    case COMPLETED = 'completed';     // 已完成

    /**
     * 获取状态标签
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::NORMAL => '正常',
            self::UNDER_REVIEW => '审核中',
            self::APPROVED => '已同意', 
            self::COMPLETED => '已完成',
        };
    }

    /**
     * 获取所有状态选项
     * @return array<string, string>
     */
    public static function getOptions(): array
    {
        return [
            self::NORMAL->value => self::NORMAL->getLabel(),
            self::UNDER_REVIEW->value => self::UNDER_REVIEW->getLabel(),
            self::APPROVED->value => self::APPROVED->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
        ];
    }

    /**
     * 从字符串创建枚举实例
     */
    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * 检查是否为可进行售后的状态
     */
    public function canApplyAftersale(): bool
    {
        return $this === self::NORMAL;
    }

    /**
     * 检查是否为进行中的售后状态
     */
    public function isAftersaleInProgress(): bool
    {
        return in_array($this, [self::UNDER_REVIEW, self::APPROVED], true);
    }

    /**
     * 检查是否为已完成的售后状态
     */
    public function isAftersaleCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}