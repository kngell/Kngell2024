<?php

declare(strict_types=1);

use Psr\Container\NotFoundExceptionInterface;

class DependencyHasNoDefaultValueException extends Exception implements NotFoundExceptionInterface
{
}