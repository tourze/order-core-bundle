<?php

namespace OrderCoreBundle\Exception;

class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'User not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
