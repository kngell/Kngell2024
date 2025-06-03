<?php

declare(strict_types=1);

class InvalidCredentialsException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid credentials')
    {
        parent::__construct($message);
    }
}