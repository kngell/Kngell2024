<?php

declare(strict_types=1);

class PageSectionProvider extends BaseSectionProvider
{
    /** @var array<string, object> */
    public function __construct(
        private readonly PageConfig $config,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($config, $iconBuilder);
    }
}