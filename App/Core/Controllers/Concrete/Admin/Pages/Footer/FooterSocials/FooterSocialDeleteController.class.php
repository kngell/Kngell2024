<?php

declare(strict_types=1);

class FooterSocialDeleteController extends AbstractDeleteController
{
    public function __construct(
        private FooterSocialDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_SOCIAL->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/footer-page/index';
    }
}