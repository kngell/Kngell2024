<?php

declare(strict_types=1);

interface ViewInterface
{
    public function render(string $templatePath, array $templateVars) : string;
}