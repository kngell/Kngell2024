<?php

declare(strict_types=1);

final class FooterColumnModalBuilder extends AbstractFooterModalBuilder
{
    protected function getModalTitle(): string
    {
        return 'Footer Column';
    }

    protected function getModalIdentifier(): string
    {
        return ModalIdentifier::FOOTER_MENU_COLUMN->value;
    }

    protected function getModalClass(): string
    {
        return 'footer-column-modal';
    }

    protected function getSubmitButtonLabel(): string
    {
        return 'Save Column';
    }

    protected function getSubmitButtonAriaLabel(): string
    {
        return 'Save Footer Column';
    }

    protected function getSaveIcon(): string
    {
        return 'icon-save';
    }
}