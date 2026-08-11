<?php

declare(strict_types=1);

final class FooterAboutModalBuilder extends AbstractFooterModalBuilder
{
    protected function getModalTitle(): string
    {
        return 'Footer About';
    }

    protected function getModalIdentifier(): string
    {
        return ModalIdentifier::FOOTER_ABOUT->value;
    }

    protected function getModalClass(): string
    {
        return 'footer-about-modal';
    }

    protected function getSubmitButtonLabel(): string
    {
        return 'Save About';
    }

    protected function getSubmitButtonAriaLabel(): string
    {
        return 'Save Footer About';
    }

    protected function getSaveIcon(): string
    {
        return 'icon-save';
    }
}