<?php

declare(strict_types=1);

class HeroSectionFormDecorator extends AbstractFormDecorator
{
    protected function getFormKey(): string
    {
        return 'heroSectionForm';
    }
}