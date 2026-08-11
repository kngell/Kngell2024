<?php

declare(strict_types=1);

final class FooterLinkModalBuilder extends AbstractFooterModalBuilder
{
    protected function getModalTitle(): string
    {
        return 'Footer Link';
    }

    protected function getModalIdentifier(): string
    {
        return ModalIdentifier::FOOTER_MENU_LINK->value;
    }

    protected function getModalClass(): string
    {
        return 'footer-link-modal';
    }

    protected function getSubmitButtonLabel(): string
    {
        return 'Save Link';
    }

    protected function getSubmitButtonAriaLabel(): string
    {
        return 'Save Footer Link';
    }

    protected function getSaveIcon(): string
    {
        return 'icon-save';
    }
}