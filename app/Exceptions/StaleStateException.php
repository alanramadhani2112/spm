<?php

namespace App\Exceptions;

use RuntimeException;

class StaleStateException extends RuntimeException
{
    public function __construct(
        string $message = 'The record has been modified since it was loaded.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
