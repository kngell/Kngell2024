<?php

declare(strict_types=1);

final class FooterSocialModalBuilder extends AbstractFooterModalBuilder
{
    protected function getModalTitle(): string
    {
        return 'Footer Social Links Modal';
    }

    protected function getModalIdentifier(): string
    {
        return ModalIdentifier::FOOTER_SOCIALS->value;
    }

    protected function getModalClass(): string
    {
        return 'footer-social-modal';
    }

    protected function getSubmitButtonLabel(): string
    {
        return 'Save Link';
    }

    protected function getSubmitButtonAriaLabel(): string
    {
        return 'Save Footer Social Links';
    }

    protected function getSaveIcon(): string
    {
        return 'icon-save';
    }
}