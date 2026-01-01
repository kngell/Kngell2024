<?php

declare(strict_types=1);

interface PaginationSectionInterface
{
    public function supports(string $key): bool;

    public function getSection(): AbstractHtmlComponent;
}