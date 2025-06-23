<?php

declare(strict_types=1);

class RouterBadFunctionCallException extends BadFunctionCallException
{
    public function __construct(string $message, int $code = 0, ?BadMethodCallException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
