<?php

namespace App\Exceptions;

use RuntimeException;

class WorkflowException extends RuntimeException
{
    public function __construct(
        string $message = 'A workflow error occurred.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
