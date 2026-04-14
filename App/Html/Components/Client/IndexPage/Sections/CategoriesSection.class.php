<?php

declare(strict_types=1);

class HomePageCategoriesSection extends AbstractBaseHtmlSection
{
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        return [];
    }

    public function getKey(): string
    {
        return 'category-section';
    }
}