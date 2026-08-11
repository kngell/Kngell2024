<?php

declare(strict_types=1);

class FormSectionProvider extends BaseSectionProvider
{
    public function __construct(
        ?FormConfig $config,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($config, $iconBuilder);
    }
}