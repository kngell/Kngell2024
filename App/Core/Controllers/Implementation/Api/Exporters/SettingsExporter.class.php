<?php

declare(strict_types=1);

class SettingsExporter extends ConfigExporterService
{
    protected string $cacheKeyPrefix = 'app_settings_';
    protected string $configType = 'settings';

    public function __construct(
        CacheInterface $cache,
        private SettingsRepository $settingsRepository,
    ) {
        parent::__construct($cache);
    }

    public function exportForClient(string $settingsType, array $options = []): array
    {
        $cacheKey = $this->generateCacheKey("type_{$settingsType}");

        return $this->getCachedOrGenerate($cacheKey, function () use ($settingsType, $options) {
            return $this->generateSettingsData($settingsType, $options);
        });
    }

    private function generateSettingsData(string $settingsType, array $options): array
    {
        switch ($settingsType) {
            case 'app':
                return $this->getAppSettings();
            case 'ui':
                return $this->getUISettings();
            case 'validation':
                return $this->getValidationSettings();
            default:
                return $this->getAllSettings();
        }
    }

    private function getAppSettings(): array
    {
        return [
            'name' => Environment::get('APP_NAME', 'My App'),
            'url' => Environment::get('APP_URL', 'http://localhost'),
            'debug' => Environment::isDebug(),
            'environment' => Environment::get('APP_ENV', 'production'),
            'timezone' => Environment::get('APP_TIMEZONE', 'UTC'),
            'locale' => Environment::get('APP_LOCALE', 'en'),
        ];
    }

    // ... other methods
}