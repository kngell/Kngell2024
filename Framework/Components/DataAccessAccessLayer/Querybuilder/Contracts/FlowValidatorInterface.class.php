<?php

declare(strict_types=1);

interface FlowValidatorInterface
{
    public function validate(array $queryFlow, array $Map, array $conditions = []): void;
}