<?php

declare(strict_types=1);

class HeroSectionDeleteController extends AbstractDeleteController
{
    public function __construct(
        private HeroDeleteService $deleteService,
    ) {
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::HERO->value;
    }

    protected function resolveRedirectUrl(): string
    {
        return '/hero-list/index';
    }
}