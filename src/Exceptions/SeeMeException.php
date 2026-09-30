<?php

namespace DoxaSoft\LaravelSeeMe\Exceptions;

use RuntimeException;

class SeeMeException extends RuntimeException
{
    public function __construct(string $message, int $apiCode, ?\Throwable $previous = null)
    {
        parent::__construct($message, $apiCode, $previous);
    }

    public function getApiCode(): int
    {
        return $this->getCode();
    }
}
