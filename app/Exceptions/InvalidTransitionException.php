<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidTransitionException extends RuntimeException
{
    public function __construct(
        string $fromStatus,
        string $toStatus,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = "Invalid status transition from '{$fromStatus}' to '{$toStatus}'.";
        parent::__construct($message, $code, $previous);
    }
}
