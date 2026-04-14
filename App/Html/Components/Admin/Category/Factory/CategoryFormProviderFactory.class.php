<?php

declare(strict_types=1);

class CategoryFormProviderFactory extends AbstractSectionProviderFactory
{
    protected function getSectionProviderClass(): string
    {
        return CategoryFormSectionProvider::class;
    }
}