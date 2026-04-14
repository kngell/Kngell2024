<?php

declare(strict_types=1);

class SmallBannerSectionProviderFactory extends AbstractSectionProviderFactory
{
    protected function getSectionProviderClass(): string
    {
        return SmallBannerSectionProvider::class;
    }
}