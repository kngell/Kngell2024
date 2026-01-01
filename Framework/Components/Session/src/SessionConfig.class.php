<?php

declare(strict_types=1);
final class SessionConfig
{
    /**
     * Ecommerce-optimized session configuration.
     */
    public static function baseConfiguration(): array
    {
        return [
            'session_name' => 'kngell_ecom',
            'cookie_lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'gc_maxlifetime' => 1800, // 30 minutes
            'use_cookies' => 1,
            'use_only_cookies' => 1,
            'use_strict_mode' => 1,
            'save_path' => 'storage/sessions',
        ];
    }
}