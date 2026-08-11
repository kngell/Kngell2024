<?php

declare(strict_types=1);

class IndexPageConfigFactory extends AbstractRegularPageConfigFactory
{
    #[Override]
    public function getEnumClass(): string
    {
        return IndexPageSection::class;
    }

    #[Override]
    public function getAssets(): array
    {
        return [
        ];
    }

    #[Override]
    public function getExpectedControllerClass(): ?string
    {
        return EcommerceController::class;
    }

    protected function buildSections(): array
    {
        return [
            BigBannerSection::class,
            DiscountSection::class,
            HeroSection::class,
            HomePageCategoriesSection::class,
            HomeProductSection::class,
            SmallBannerSection::class,
            SummerBannerSection::class,
        ];
    }
}