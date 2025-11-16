<?php

declare(strict_types=1);

class ViewNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'View not found', int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}