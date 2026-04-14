<?php

declare(strict_types=1);

interface ProductTableSectionInterface
{
    public function supports(string $key): bool;

    public function getSection(): AbstractHtmlComponent;
}