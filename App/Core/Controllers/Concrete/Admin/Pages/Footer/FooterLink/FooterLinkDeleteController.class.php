<?php

declare(strict_types=1);

class FooterLinkDeleteController extends AbstractDeleteController
{
    public function __construct(
        private FooterLinkDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_MENU_LINK->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/footer-page/index';
    }
}