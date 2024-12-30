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
        // BASE DIR
        // -----------------------------------------------------------------------
        defined('CONFIG_PATH') or define('CONFIG_PATH', ROOT_DIR . DS . 'App' . DS . 'Config');
        defined('CACHE_DIR') or define('CACHE_DIR', ROOT_DIR . DS . 'Cache' . DS);
        defined('LOG_DIR') or define('LOG_DIR', ROOT_DIR . DS . 'Temp' . DS . 'Log');
        defined('APP') or define('APP', ROOT_DIR . DS . 'App' . DS);
        defined('VIEW') or define('VIEW', ROOT_DIR . DS . 'App' . DS . 'Views' . DS);
        defined('SCRIPT') or define('SCRIPT', dirname($_SERVER['SCRIPT_NAME']));
        defined('IMG') or define('IMG', SCRIPT . DS . 'assets' . DS . 'img' . DS);
        defined('COMPONENTS') or define('COMPONENTS', ROOT_DIR . DS . 'Framework' . DS . 'Components' . DS);
        // -----------------------------------------------------------------------
        // VISITORS, LOGIN & REGISTRATION
        // -----------------------------------------------------------------------
        defined('VISITOR_COOKIE_NAME') or define('VISITOR_COOKIE_NAME', 'gcx_kngell_eshop01_visitor');
        defined('COOKIE_EXPIRY') or define('COOKIE_EXPIRY', 60 * 60 * 24 * 30);
        defined('CURRENT_USER_SESSION_NAME') or define('CURRENT_USER_SESSION_NAME', 'user_kngell_xfh');
        defined('REMEMBER_ME_COOKIE_NAME') or define('REMEMBER_ME_COOKIE_NAME', 'remeber_token');
        //-----------------------------------------------------------------------
        // Form
        // -----------------------------------------------------------------------
        defined('CSRF_TOKEN_SECRET') or define('CSRF_TOKEN_SECRET', 'WwO932iIyaOLCKqMJ5wBKziOygkaTcSp');
        defined('CSRF_TOKEN_LENGHT') or define('CSRF_TOKEN_LENGHT', 32);
        defined('CSRF_TOKEN_LIFETIME') or define('CSRF_TOKEN_LIFETIME', 60 * 30);
        defined('TOKEN_NAME') or define('TOKEN_NAME', 'token');

        //-----------------------------------------------------------------------
        // URLs
        // -----------------------------------------------------------------------
        defined('PREVIOUS_PAGE') or define('PREVIOUS_PAGE', 'urlwwww1213212');
        defined('CURRENT_PAGE') or define('CURRENT_PAGE', 'urlcccccc');
    }
}