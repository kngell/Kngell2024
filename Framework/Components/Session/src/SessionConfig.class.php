<?php

declare(strict_types=1);

final class SessionConfig
{
    /** @var string */
    private const DEFAULT_DRIVER = 'native_storage';

    /**
     * Main session configuration default array settings.
     *
     * @return array
     */
    public static function baseConfiguration(): array
    {
        return [
            'session_name' => 'kgl_xsf_session',
            'cookie_lifetime' => 3600,
            'path' => '/',
            'domain' => 'localhost',
            'cookie_secure' => false,
            'cookie_httponly' => true,
            'gc_maxlifetime' => '1800',
            'gc_divisor' => '1',
            'gc_probability' => '100',
            'use_cookies' => '1',
            'globalized' => false,
            'default_driver' => self::DEFAULT_DRIVER,
            'save_path' => 'session_dir',
            'drivers' => [
                'native_storage' => [
                    'class' => 'NativeSessionStorage',
                    'default' => true,
                ],
                'array_storage' => [
                    'class' => 'ArraySessionStorage',
                    'default' => false,

                ],
                'pdo_storage' => [
                    'class' => 'PdoSessionStorage',
                    'default' => false,

                ],
            ],
        ];
    }
}