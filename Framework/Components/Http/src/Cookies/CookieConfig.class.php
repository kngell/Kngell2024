<?php

declare(strict_types=1);

class CookieConfig
{
    /** @return void */
    public function __construct()
    {
    }

    /**
     * Main cookie configuration default array settings.
     *
     * @return array
     */
    public function baseConfig(): array
    {
        return [

            'name' => VISITOR_COOKIE_NAME, //'__kngell_cookie__',
            'expires' => time() + COOKIE_EXPIRY,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,

        ];
    }
}