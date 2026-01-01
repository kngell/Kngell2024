<?php

declare(strict_types=1);

class DatabaseLocaleProvider implements LocaleProviderInterface
{
    private const CACHE_TTL = 86400; // 24 hours for locale data
    private const CACHE_PREFIX = 'locale_';

    public function __construct(
        private ?RegionLocaleModel $localeModel,
        private ?RegionLocaleMappingModel $regionLocaleModel,
        private ?RegionModel $regionModel,
        private string $defaultLocale,
        private array $builtinLocaleData,
        private ?CacheInterface $cache,
    ) {
    }

    public function isValidLocale(string $locale): bool
    {
        $cacheKey = $this->buildCacheKey('is_valid', $locale);

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return (bool) $cached;
            }
        }

        $isValid = false;

        if ($this->localeModel) {
            $localeEntity = $this->localeModel->one([
                'locale_code' => $locale,
                'is_active' => true,
            ])->asClass();

            $isValid = $localeEntity !== null;
        }

        if (!$isValid) {
            $isValid = isset($this->builtInLocaleData[$locale]);
        }

        // Cache the result
        if ($this->cache) {
            $this->cache->set($cacheKey, $isValid, self::CACHE_TTL);
        }

        return $isValid;
    }

    public function getLocaleForRegion(string $regionCode): ?string
    {
        // Normalize to uppercase
        $regionCode = strtoupper(trim($regionCode));
        $cacheKey = $this->buildCacheKey('region_locale', $regionCode);

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached ?: null;
            }
        }

        $locale = null;

        // Try region_locale_mapping table first
        if ($this->regionLocaleModel) {
            /** @var null|RegionLocaleMapping */
            $mapping = $this->regionLocaleModel->one([
                'region_code' => $regionCode,
                'is_default' => true,
                'is_active' => true,
            ])->asClass();

            if ($mapping) {
                $locale = $mapping->getLocaleCode();
            }
        }

        // Try region table if no mapping found
        if (!$locale && $this->regionModel) {
            /** @var Region|null */
            $region = $this->regionModel->one([
                'region_code' => $regionCode,
                'is_active' => true,
            ])->asClass();

            if ($region) {
                $locale = $region->getDefaultLocale() ?? $region->getLocale();
            }
        }

        // Fallback to built-in mapping
        if (!$locale) {
            $locale = $this->getBuiltInLocaleForRegion($regionCode);
        }

        // Cache the result (even if null)
        if ($this->cache) {
            $this->cache->set($cacheKey, $locale, self::CACHE_TTL);
        }

        return $locale;
    }

    public function getLocaleData(string $locale): array
    {
        $cacheKey = $this->buildCacheKey('locale_data', $locale);

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $localeData = null;

        // FIRST: Try to get formatting from REGION table
        // We need to find a region that uses this locale as its default locale
        if ($this->regionModel) {
            /** @var null|Region */
            $region = $this->regionModel->one([
                'default_locale' => $locale,
                'is_active' => true,
            ])->asClass();

            // If not found by default_locale, try locale field
            if (!$region) {
                /** @var null|Region */
                $region = $this->regionModel->one([
                    'locale' => $locale,
                    'is_active' => true,
                ])->asClass();
            }

            if ($region) {
                $localeData = [
                    'decimal_separator' => $region->getDecimalSeparator(),
                    'thousands_separator' => $region->getThousandsSeparator(),
                    'fraction_digits' => 2, // Default
                    'date_format' => $region->getDateFormat(),
                    'datetime_format' => $region->getDatetimeFormat(),
                    'time_format' => $region->getTimeFormat(),
                    'first_day_of_week' => $region->getFirstDayOfWeek(),
                    'currency_position' => $this->getCurrencyPositionForLocale($locale),
                    'locale_name' => $locale,
                    'language' => substr($locale, 0, 2),
                    'country' => substr($locale, 3, 2),
                ];
            }
        }

        // SECOND: If no region found, get basic info from locale table
        if (!$localeData && $this->localeModel) {
            /** @var null|RegionLocale */
            $localeEntity = $this->localeModel->one([
                'locale_code' => $locale,
                'is_active' => true,
            ])->asClass();

            if ($localeEntity) {
                // Get formatting from built-in data based on locale code
                $builtInFormatting = $this->getBuiltInFormatting($locale);

                $localeData = array_merge($builtInFormatting, [
                    'locale_name' => $localeEntity->getLocaleName(),
                    'language' => $localeEntity->getLanguageCode(),
                    'country' => $localeEntity->getCountryCode(),
                ]);
            }
        }

        // THIRD: Fallback to built-in data
        if (!$localeData) {
            $localeData = $this->builtInLocaleData[$locale] ?? $this->builtinLocaleData[$this->defaultLocale];
        }

        // Cache the result
        if (!$this->cache->exists($cacheKey)) {
            $this->cache->set($cacheKey, $localeData, self::CACHE_TTL);
        }

        return $localeData;
    }

    public function getSupportedLocales(): array
    {
        $cacheKey = $this->buildCacheKey('supported_locales');

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $locales = [];

        if ($this->localeModel) {
            /** @var RegionLocale[] */
            $activeLocales = $this->localeModel->all(['is_active' => true])->asClass();
            foreach ($activeLocales as $localeEntity) {
                $locales[$localeEntity->getLocaleCode()] = $localeEntity->getLocaleName();
            }
        } else {
            foreach ($this->builtinLocaleData as $code => $data) {
                $locales[$code] = $data['locale_name'] ?? $code;
            }
        }

        // Cache the result
        if (!$this->cache->exists($cacheKey)) {
            $this->cache->set($cacheKey, $locales, self::CACHE_TTL);
        }

        return $locales;
    }

    public function getDefaultLocale(): string
    {
        $cacheKey = $this->buildCacheKey('default_locale');

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $defaultLocale = $this->defaultLocale;

        if ($this->localeModel) {
            /** @var null|RegionLocale */
            $localeEntity = $this->localeModel->one([
                'is_default' => true,
                'is_active' => true,
            ])->asClass();

            if ($localeEntity) {
                $defaultLocale = $localeEntity->getLocaleCode();
            }
        }

        // Cache the result
        if (!$this->cache->exists($cacheKey)) {
            $this->cache->set($cacheKey, $defaultLocale, self::CACHE_TTL);
        }

        return $defaultLocale;
    }

    public function getFormattingForRegion(string $regionCode): ?array
    {
        $regionCode = strtoupper(trim($regionCode));
        $cacheKey = $this->buildCacheKey('region_formatting', $regionCode);

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        if (!$this->regionModel) {
            return null;
        }
        /** @var null|Region */
        $region = $this->regionModel->one([
            'region_code' => $regionCode,
            'is_active' => true,
        ])->asClass();

        if (!$region) {
            return null;
        }

        $formatting = [
            'decimal_separator' => $region->getDecimalSeparator(),
            'thousands_separator' => $region->getThousandsSeparator(),
            'date_format' => $region->getDateFormat(),
            'datetime_format' => $region->getDatetimeFormat(),
            'time_format' => $region->getTimeFormat(),
            'first_day_of_week' => $region->getFirstDayOfWeek(),
        ];

        // Cache the result
        if (!$this->cache->exists($cacheKey)) {
            $this->cache->set($cacheKey, $formatting, self::CACHE_TTL);
        }

        return $formatting;
    }

    public function getLocaleDataWithRegionFormatting(string $locale, string $regionCode): array
    {
        $cacheKey = $this->buildCacheKey('locale_region_data', $locale . '_' . $regionCode);

        // Check cache first
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // Get basic locale data
        $localeData = $this->getLocaleData($locale);

        // Get region-specific formatting
        $regionFormatting = $this->getFormattingForRegion($regionCode);

        // Merge region formatting into locale data (region takes precedence)
        if ($regionFormatting) {
            $localeData = array_merge($localeData, $regionFormatting);
        }

        // Cache the result
        if (!$this->cache->exists($cacheKey)) {
            $this->cache->set($cacheKey, $localeData, self::CACHE_TTL);
        }

        return $localeData;
    }

    public function clearCache(): void
    {
        if ($this->cache) {
            $this->cache->deletePattern(self::CACHE_PREFIX . '*');
        }
    }

    public function clearLocaleCache(string $locale): void
    {
        if ($this->cache) {
            $keys = [
                $this->buildCacheKey('is_valid', $locale),
                $this->buildCacheKey('locale_data', $locale),
            ];

            foreach ($keys as $key) {
                $this->cache->delete($key);
            }

            // Also clear composite caches
            $this->cache->deletePattern($this->buildCacheKey('locale_region_data', $locale . '_*'));

            // Clear the supported locales and default locale caches
            $this->cache->delete($this->buildCacheKey('supported_locales'));
            $this->cache->delete($this->buildCacheKey('default_locale'));
        }
    }

    public function clearRegionCache(string $regionCode): void
    {
        if ($this->cache) {
            $regionCode = strtoupper(trim($regionCode));
            $keys = [
                $this->buildCacheKey('region_locale', $regionCode),
                $this->buildCacheKey('region_formatting', $regionCode),
            ];

            foreach ($keys as $key) {
                $this->cache->delete($key);
            }

            // Clear composite caches
            $this->cache->deletePattern($this->buildCacheKey('locale_region_data', '*_' . $regionCode));
        }
    }

    public function getCacheStats(): array
    {
        if (!$this->cache) {
            return ['cache_enabled' => false];
        }

        $stats = [
            'cache_enabled' => true,
            'cache_class' => get_class($this->cache),
        ];

        if (method_exists($this->cache, 'getStats')) {
            $stats = array_merge($stats, $this->cache->getStats());
        }

        return $stats;
    }

    public function warmUpCache(): void
    {
        if (!$this->cache) {
            return;
        }

        $commonLocales = ['en_US', 'en_EU', 'en_GB', 'fr_FR', 'de_DE'];
        $commonRegions = ['US', 'EU', 'GB', 'FR', 'DE'];

        foreach ($commonLocales as $locale) {
            $this->isValidLocale($locale);
            $this->getLocaleData($locale);
        }

        foreach ($commonRegions as $region) {
            $this->getLocaleForRegion($region);
            $this->getFormattingForRegion($region);
        }

        // Pre-load supported locales and default locale
        $this->getSupportedLocales();
        $this->getDefaultLocale();
    }

    private function getBuiltInLocaleForRegion(string $regionCode): ?string
    {
        return match ($regionCode) {
            'US' => 'en_US',
            'EU' => 'en_EU',
            'GB' => 'en_GB',
            'FR' => 'fr_FR',
            'DE' => 'de_DE',
            'IT' => 'it_IT',
            'ES' => 'es_ES',
            'JP' => 'ja_JP',
            'CN' => 'zh_CN',
            'KR' => 'ko_KR',
            'AU' => 'en_AU',
            'CA' => 'en_CA',
            'IN' => 'en_IN',
            'BR' => 'pt_BR',
            'RU' => 'ru_RU',
            'SA' => 'ar_SA',
            'CH' => 'de_CH',
            'MX' => 'es_MX',
            'SG' => 'en_SG',
            'HK' => 'zh_HK',
            'NZ' => 'en_NZ',
            'AE' => 'ar_AE',
            default => $this->defaultLocale,
        };
    }

    private function getBuiltInFormatting(string $locale): array
    {
        return $this->builtInLocaleData[$locale] ?? $this->builtinLocaleData[$this->defaultLocale];
    }

    private function getCurrencyPositionForLocale(string $locale): string
    {
        // Determine currency position based on locale
        return match (substr($locale, 0, 2)) {
            'en' => 'before',  // English: $100
            'fr', 'de', 'es', 'it', 'pt' => 'after',  // European: 100€
            'ja', 'zh', 'ko' => 'before',  // Asian: ¥100
            'ar' => 'after',  // Arabic: 100 ر.س
            default => 'before',
        };
    }

    private function buildCacheKey(string $type, mixed $identifier = null): string
    {
        $key = self::CACHE_PREFIX . $type;
        if ($identifier !== null) {
            $key .= '_' . (is_scalar($identifier) ? $identifier : md5(serialize($identifier)));
        }
        return $key;
    }
}