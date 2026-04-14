<?php

declare(strict_types=1);

class SmallBannerDecorator extends AbstractFormDecorator
{
    protected function getFormKey(): string
    {
        return 'smallBannerForm';
    }

    protected function beforeRender(): void
    {
        // Example: Set small banner specific data
        // $target = $this->getTarget();
        // $target->setBannerContext('small');
    }
}