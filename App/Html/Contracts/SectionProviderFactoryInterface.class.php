<?php

declare(strict_types=1);

interface SectionProviderFactoryInterface
{
    public function create(): SectionProviderInterface;
}