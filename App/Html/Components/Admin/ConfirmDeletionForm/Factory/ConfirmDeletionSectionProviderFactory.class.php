<?php

declare(strict_types=1);

class ConfirmDeletionSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        private IconBuilder $iconBuilder,
    ) {
    }

    public function create(): ConfirmDeletionSectionProvider
    {
        return new ConfirmDeletionSectionProvider($this->iconBuilder);
    }
}