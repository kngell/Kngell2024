<?php

declare(strict_types=1);

interface ModelFactoryInterface
{
    public function create(string $type): ModelStrategyInterface;

    public function supports(string $type): bool;
}