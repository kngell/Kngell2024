<?php

declare(strict_types=1);

class SessionConfig
{
    /** @var string */
    private const DEFAULT_DRIVER = 'native_storage';

    /**
     * Main session configuration default array settings.
     *
     * @return array
     */
    public function baseConfiguration(): array
    {
        return [
            'session_name' => 'kngell_enterprise',
            'lifetime' => 3600,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,
            'httponly' => true,
            'gc_maxlifetime' => '1800',
            'gc_divisor' => '1',
            'gc_probability' => '1000',
            'use_cookies' => '1',
            'globalized' => false,
            'default_driver' => self::DEFAULT_DRIVER,
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
    // [
    //     'session_name' => 'LavaStudio',
    //     'lifetime' => 3600,
    //     'path' => '/',
    //     'domain' => 'localhost',
    //     'secure' => false,
    //     'httponly' => true,
    //     'gc_maxlifetime' => '1800',
    //     'gc_divisor' => '1',
    //     'gc_probability' => '1000',
    //     'use_cookies' => '1',
    //     'globalized' => false,
    //     'default_driver' => self::DEFAULT_DRIVER,
    //     'drivers' => [
    //         'native_storage' => [
    //             'class' => 'NativeSessionStorage',
    //             'default' => true,
    //         ],
    //         'array_storage' => [
    //             'class' => 'ArraySessionStorage',
    //             'default' => false,

    //         ],
    //         'pdo_storage' => [
    //             'class' => 'PdoSessionStorage',
    //             'default' => false,

    //         ],
    //     ],
    // ];
}