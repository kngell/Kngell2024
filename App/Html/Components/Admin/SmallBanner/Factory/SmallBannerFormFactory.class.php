<?php

declare(strict_types=1);

class SmallBannerFormFactory extends AbstractFormFactory
{
    private const array SUPPORTED_ROUTES = [
        'small_banner/save',
        'small_banner/edit',
        'small_banner/add',
        'small-banner-save/index',
    ];

    public function __construct(
        private SmallBannerForm $smallBannerForm,
    ) {
    }

    protected function getSupportedRoutes(): array
    {
        return self::SUPPORTED_ROUTES;
    }

    protected function getForm(): FormTemplateInterface
    {
        return $this->smallBannerForm;
    }
}