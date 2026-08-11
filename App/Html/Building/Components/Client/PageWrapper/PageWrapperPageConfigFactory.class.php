<?php

declare(strict_types=1);

class PageWrapperPageConfigFactory extends AbstractRegularPageConfigFactory
{
    #[Override]
    public function getEnumClass(): string
    {
        return PageWrapperSection::class;
    }

    #[Override]
    public function getAssets(): array
    {
        return [
        ];
    }

    #[Override]
    public function getExpectedControllerClass(): ?string
    {
        return $this->expectedController;
    }

    protected function buildSections(): array
    {
        return [
            FooterSection::class,
            HeaderBottomSection::class,
            HeaderTopSection::class,
        ];
    }
}