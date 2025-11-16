<?php

declare(strict_types=1);

class AmbiguousFileException extends RuntimeException
{
    public function __construct(string $message = 'Multiple views match the request', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}