<?php

declare(strict_types=1);
abstract class AbstractNavbarHtmlElement implements HtmlComponentsInterface
{
    protected const array NAVBAR_CLASS = [
        'nav',
    ];
    protected const array NAVBAR_STYLE = [];

    protected const array BTN_CUSTOM = [
    ];

    protected const string LOGO = ROOT_DIR . SCRIPT . DS . 'assets' . DS . 'img' . DS . 'logo.png';
    protected const string LOGO_SOURCE = SRC . 'assets' . DS . 'img' . DS . 'logo.png';

    protected function logo() : string
    {
        $copy = false;
        if (! file_exists(self::LOGO)) {
            $copy = FileManager::copyFile(self::LOGO_SOURCE, self::LOGO);
        } else {
            return str_replace(ROOT_DIR, '', self::LOGO);
        }
        return $copy ? str_replace(ROOT_DIR, '', self::LOGO) : '#';
    }
}