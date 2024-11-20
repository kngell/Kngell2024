<?php

declare(strict_types=1);

interface DependenciesResolverInterface
{
    public function resolve(int $key) : mixed;
}