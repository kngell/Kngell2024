<?php

declare(strict_types=1);

interface FormFactoryInterface
{
    public function supports(string $action, string $route = ''): bool;

    public function createForm(): FormTemplateInterface;
}