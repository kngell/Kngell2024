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
        return 'Hero Section';
    }
}