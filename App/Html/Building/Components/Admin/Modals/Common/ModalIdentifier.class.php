<?php

declare(strict_types=1);

enum ModalIdentifier: string
{
    public function getLabel(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    public function getPlural(): string
    {
        return $this->value . 's';
    }
    case CONFIRM_DELETION = 'confirmDeletionModal';
    case FOOTER_MENU_COLUMN = 'footerMenuColumn';
    case FOOTER_MENU_LINK = 'footerMenuLink';
    case FOOTER_SOCIALS = 'footerSocialMedia';
    case FOOTER_ABOUT = 'footerAbout';
    case CHECKOUT_ADDRESS = 'checkoutUserAddress';
}