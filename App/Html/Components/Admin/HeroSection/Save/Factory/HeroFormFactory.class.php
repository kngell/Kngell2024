<?php

declare(strict_types=1);

class HeroFormFactory extends AbstractFormFactory
{
    private const array SUPPORTED_ROUTES = [
        'hero_section/save',
        'hero_section/edit',
        'hero_section/add',
        'hero-section-save/index',
    ];

    public function __construct(
        private HeroSectionForm $heroForm,
    ) {
    }

    protected function getSupportedRoutes(): array
    {
        return self::SUPPORTED_ROUTES;
    }

    protected function getForm(): FormTemplateInterface
    {
        return $this->heroForm;
    }
}