<?php

declare(strict_types=1);

class CategoryFormDecorator extends AbstractFormDecorator
{
    protected function getFormKey(): string
    {
        return 'categoryForm';
    }
}