<?php

declare(strict_types=1);

class RegularPageProvider extends BaseSectionProvider
{
    public function __construct(
        private readonly RegularPageConfig $config,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($config, $iconBuilder);
    }

    public function getSectionEnumClass(): string
    {
        return $this->config->getEnumClass();
    }
}