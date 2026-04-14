<?php

declare(strict_types=1);

interface FormFactoryInterface
{
    public function supports(string $action): bool;

    public function createForm(): FormTemplateInterface;
}