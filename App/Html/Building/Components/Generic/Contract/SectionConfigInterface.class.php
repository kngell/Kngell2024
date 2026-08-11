<?php

declare(strict_types=1);

interface SectionConfigInterface
{
    public function getSections(): array;

    public function getSectionConfigs(): array;
}