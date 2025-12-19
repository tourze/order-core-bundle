<?php

namespace OrderCoreBundle\Exception;

final class ContractNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Contract not found', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
