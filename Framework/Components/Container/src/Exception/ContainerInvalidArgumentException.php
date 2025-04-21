<?php

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;

/** PSR-11 Container */
class ContainerInvalidArgumentException extends BaseInvalidArgumentException implements ContainerExceptionInterface
{
}