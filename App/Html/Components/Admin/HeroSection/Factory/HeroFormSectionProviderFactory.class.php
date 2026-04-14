<?php

declare(strict_types=1);

class HeroFormSectionProviderFactory extends AbstractSectionProviderFactory
{
    protected function getSectionProviderClass(): string
    {
        return HeroSectionFormProvider::class;
    }
}