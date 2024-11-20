<?php

declare(strict_types=1);

use Psr\Container\NotFoundExceptionInterface;

class DependencyHasNoValueException extends Exception implements NotFoundExceptionInterface
{
}