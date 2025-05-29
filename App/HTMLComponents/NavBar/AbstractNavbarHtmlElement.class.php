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

    protected const string FILE_TO_SEARCH = ROOT_DIR . SCRIPT . DS . 'assets' . DS . 'img' . DS;
    protected const string SOURCE_FILE = SRC . 'assets' . DS . 'img' . DS;

    protected function file(string $file) : string
    {
        $copy = false;
        $file_to_search = self::FILE_TO_SEARCH . $file;
        $source = self::SOURCE_FILE . $file;
        if (! file_exists($file_to_search)) {
            $copy = FileManager::copyFile($source, $file_to_search);
        } else {
            return str_replace(ROOT_DIR, HOST, $file_to_search);
        }
        return $copy ? str_replace(ROOT_DIR, HOST, $file_to_search) : '#';
    }
}