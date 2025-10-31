<?php

namespace OrderCoreBundle\Exception;

/**
 * 积分退款时用户不存在异常
 */
class CreditRefundUserNotFoundException extends \RuntimeException
{
    public function __construct(string $message = '积分退款时用户不存在', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
