<?php

declare(strict_types=1);

abstract class AbstractSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        protected IconBuilder $iconBuilder,
        protected readonly FormSectionHeader $header,
    ) {
    }

    public function create(): SectionProviderInterface
    {
        $providerClass = $this->getSectionProviderClass();

        return new $providerClass(
            $this->iconBuilder,
            $this->header,
        );
    }

    abstract protected function getSectionProviderClass(): string;
}