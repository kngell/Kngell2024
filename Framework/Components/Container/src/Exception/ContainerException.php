<?php

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;

/** PSR-11 Container */
class ContainerException extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
