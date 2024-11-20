<?php

declare(strict_types=1);
final readonly class AppConstants
{
    private function __construct()
    {
    }

    public static function enable() : void
    {
        // -----------------------------------------------------------------------
        // SEPARATORS
        // -----------------------------------------------------------------------
        defined('URL_SEPARATOR') or define('URL_SEPARATOR', '/');
        defined('PS') or define('PS', PATH_SEPARATOR);
        defined('US') or define('US', URL_SEPARATOR);
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        // -----------------------------------------------------------------------
        // ROOT DIR
        // -----------------------------------------------------------------------
        defined('ROOT_DIR') or define('ROOT_DIR', dirname(getcwd()));
        defined('CONFIG_PATH') or define('CONFIG_PATH', ROOT_DIR . DS . 'App' . DS . 'Config');
        defined('CACHE_DIR') or define('CACHE_DIR', ROOT_DIR . DS . 'Cache' . DS);
        defined('LOG_DIR') or define('LOG_DIR', ROOT_DIR . DS . 'Temp' . DS . 'Log');
        // -----------------------------------------------------------------------
        // VISITORS, LOGIN & REGISTRATION
        // -----------------------------------------------------------------------
        defined('VISITOR_COOKIE_NAME') or define('VISITOR_COOKIE_NAME', 'gcx_kngell_eshop01_visitor');
        defined('COOKIE_EXPIRY') or define('COOKIE_EXPIRY', 60 * 60 * 24 * 360);
    }
}