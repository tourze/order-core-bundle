<?php

namespace OrderCoreBundle\Exception;

use RuntimeException;

class FeatureMigratedException extends \RuntimeException
{
    public function __construct(string $feature, string $newLocation, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('%s功能已迁移到%s，请使用新的服务', $feature, $newLocation),
            0,
            $previous
        );
    }
}
