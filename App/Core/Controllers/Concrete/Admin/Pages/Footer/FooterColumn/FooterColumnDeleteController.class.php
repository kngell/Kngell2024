<?php

declare(strict_types=1);

class FooterColumnDeleteController extends AbstractDeleteController
{
    public function __construct(
        private FooterColumnDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_MENU_COLUMN->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/admin/footer-page/index';
    }
}