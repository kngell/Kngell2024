<?php

declare(strict_types=1);
class LoggerFactory
{
    public static function create(): CustomLogger
    {
        $isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

        return new CustomLogger(
            logFile: $_ENV['LOG_FILE'] ?? STORAGE . 'logs' . DS . 'app.log',
            displayInBrowser: !$isProduction,
            debugLevel: $isProduction ? 0 : 1,
            enabled: (bool) ($_ENV['LOG_ENABLED'] ?? true),
        );
    }
}