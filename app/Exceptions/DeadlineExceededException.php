<?php

namespace App\Exceptions;

use RuntimeException;

class DeadlineExceededException extends RuntimeException
{
    public function __construct(
        string $message = 'The deadline for this action has been exceeded.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
