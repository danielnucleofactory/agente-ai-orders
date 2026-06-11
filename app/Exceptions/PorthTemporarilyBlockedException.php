<?php

namespace App\Exceptions;

use RuntimeException;

class PorthTemporarilyBlockedException extends RuntimeException
{
    public function __construct(
        protected ?int $retryAfterSeconds = null,
        string $message = 'Porth temporarily blocked this API resource.'
    ) {
        parent::__construct($message);
    }

    public function retryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
