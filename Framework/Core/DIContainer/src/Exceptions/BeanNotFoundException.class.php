<?php

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;

class BeanNotFoundException extends Exception implements ContainerExceptionInterface
{
}