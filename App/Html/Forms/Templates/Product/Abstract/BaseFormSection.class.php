<?php

declare(strict_types=1);

abstract class BaseFormSection implements FormSectionInterface
{
    public function __construct(
        protected readonly HtmlBuilder $formBuilder,
    ) {
    }

    public function shouldRender(array $formValues = []): bool
    {
        return true;
    }
}