<?php

declare(strict_types=1);

class FooterAboutDeleteController extends AbstractDeleteController
{
    public function __construct(
        private FooterAboutDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_ABOUT->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/footer-page/index';
    }
}