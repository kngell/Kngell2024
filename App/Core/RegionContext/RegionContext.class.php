<?php

declare(strict_types=1);

final class RegionContext implements RegionContextInterface
{
    private const FALLBACK_REGION = 'EU';
    private const FALLBACK_LOCALE = 'en_US';

    private ?string $resolvedRegion = null;
    private ?string $resolvedLocale = null;
    private ?array $localeData = null;
    private ?Region $regionEntity = null;
    private ?Currency $currencyEntity = null;
    private bool $isRegionExplicit = false;

    public function __construct(
        private RegionResolver $regionResolver,
        private RegionDataProviderInterface $regionDataProvider,
        private CurrencyResolverInterface $currencyResolver,
        private FormatterInterface $formatter,
        private LocaleProviderInterface $localeProvider,
        private CacheInterface $cache,
        private string $defaultRegion,
        private string $defaultLocale,
    ) {
    }

    public function getRegionCode(): string
    {
        if ($this->resolvedRegion === null) {
            $this->resolvedRegion = $this->resolveRegion();
        }

        return $this->resolvedRegion;
    }

    public function getCurrencyCode(): string
    {
        return $this->getCurrencyEntity()?->getCurrencyCode() ?? 'EUR';
    }

    public function getLocale(): string
    {
        if ($this->resolvedLocale === null) {
            $this->resolvedLocale = $this->resolveLocale();
        }
        return $this->resolvedLocale;
    }

    public function getLocaleData(): array
    {
        if ($this->localeData === null) {
            $locale = $this->getLocale();
            $regionCode = $this->getRegionCode();
            $cacheKey = "locale_data_{$locale}_{$regionCode}";

            if ($cached = $this->cache->get($cacheKey)) {
                $this->localeData = $cached;
            } else {
                $this->localeData = $this->localeProvider->getLocaleData($locale);

                // Enhance with currency data
                $currency = $this->getCurrencyEntity();
                if ($currency) {
                    $this->localeData['fraction_digits'] = $currency->getFractionDigits() ?? 2;
                }

                $this->cache->set($cacheKey, $this->localeData, 3600);
            }
        }
        return $this->localeData;
    }

    public function getRegionEntity(): ?Region
    {
        if ($this->regionEntity === null) {
            $regionCode = $this->getRegionCode();
            $this->regionEntity = $this->regionDataProvider->getRegionByCode($regionCode);
        }
        return $this->regionEntity;
    }

    public function getCurrencyEntity(): ?Currency
    {
        if ($this->currencyEntity === null) {
            $regionCode = $this->getRegionCode();
            $this->currencyEntity = $this->currencyResolver->resolveCurrencyForRegion($regionCode);
        }
        return $this->currencyEntity;
    }

    public function getNumberFormat(): array
    {
        $localeData = $this->getLocaleData();
        return [
            'decimal_separator' => $localeData['decimal_separator'] ?? '.',
            'thousands_separator' => $localeData['thousands_separator'] ?? ',',
            'fraction_digits' => $localeData['fraction_digits'] ?? 2,
            'currency_position' => $localeData['currency_position'] ?? 'before',
        ];
    }

    public function getDateFormat(): string
    {
        $localeData = $this->getLocaleData();
        return $localeData['date_format'] ?? 'Y-m-d';
    }

    public function getDateTimeFormat(): string
    {
        $localeData = $this->getLocaleData();
        return $localeData['datetime_format'] ?? 'Y-m-d H:i:s';
    }

    public function getTimeFormat(): string
    {
        $localeData = $this->getLocaleData();
        return $localeData['time_format'] ?? 'H:i:s';
    }

    public function getFirstDayOfWeek(): int
    {
        $localeData = $this->getLocaleData();
        return $localeData['first_day_of_week'] ?? 1;
    }

    public function getTimezone(): string
    {
        $regionEntity = $this->getRegionEntity();
        return $regionEntity?->getTimezone() ?? 'UTC';
    }

    public function isRegionExplicit(): bool
    {
        if ($this->resolvedRegion === null) {
            $this->getRegionCode(); // Trigger resolution
        }
        return $this->isRegionExplicit;
    }

    public function formatNumber(float $number, ?int $decimals = null): string
    {
        $format = $this->getNumberFormat();
        return $this->formatter->formatNumber($number, $format, $decimals);
    }

    public function formatCurrency(float $amount, ?string $currencyCode = null): string
    {
        $currencyCode = $currencyCode ?? $this->getCurrencyCode();
        $format = $this->getNumberFormat();

        return $this->formatter->formatCurrency($amount, $currencyCode, $format);
    }

    public function formatDate(DateTimeInterface $date, ?string $format = null): string
    {
        $format = $format ?? $this->getDateFormat();
        return $this->formatter->formatDate($date, $format);
    }

    public function formatDateTime(DateTimeInterface $dateTime, ?string $format = null): string
    {
        $format = $format ?? $this->getDateTimeFormat();
        return $this->formatter->formatDateTime($dateTime, $format);
    }

    public function formatTime(DateTimeInterface $time, ?string $format = null): string
    {
        $format = $format ?? $this->getTimeFormat();
        return $time->format($format);
    }

    public function getContextForProductPrice(): array
    {
        return [
            'region_code' => $this->getRegionCode(),
            'currency_code' => $this->getCurrencyCode(),
            'currency_id' => $this->getCurrencyEntity()?->getId(),
            'locale' => $this->getLocale(),
            'price_includes_tax' => $this->shouldPriceIncludeTax(),
            'tax_rate' => $this->getTaxRate(),
        ];
    }

    public function clearCache(): void
    {
        $this->resolvedRegion = null;
        $this->resolvedLocale = null;
        $this->localeData = null;
        $this->regionEntity = null;
        $this->currencyEntity = null;
        $this->isRegionExplicit = false;
    }

    private function shouldPriceIncludeTax(): bool
    {
        $regionEntity = $this->getRegionEntity();
        if (!$regionEntity) {
            return false;
        }

        // Check if region typically includes tax in displayed prices
        // This could be a database column in the future
        return in_array($regionEntity->getRegionCode(), ['EU', 'GB', 'AU', 'NZ']);
    }

    private function getTaxRate(): float
    {
        $regionEntity = $this->getRegionEntity();
        if (!$regionEntity) {
            return 0.0;
        }

        // Default VAT/GST rates per region
        return match ($regionEntity->getRegionCode()) {
            'EU' => 0.20, // 20% VAT
            'GB' => 0.20, // 20% VAT
            'FR' => 0.20,
            'DE' => 0.19,
            'IT' => 0.22,
            'ES' => 0.21,
            'US' => 0.0,  // Varies by state
            'CA' => 0.05, // GST
            'AU' => 0.10, // GST
            'NZ' => 0.15, // GST
            'JP' => 0.10, // Consumption tax
            'SG' => 0.07, // GST
            default => 0.0,
        };
    }

    private function resolveRegion(): string
    {
        $resolved = $this->regionResolver->resolve();
        $this->isRegionExplicit = $this->regionResolver->hasExplicitRegion();  // ← Now cached!

        if ($resolved && $this->regionDataProvider->isValidRegion($resolved)) {
            return strtoupper($resolved);
        }

        if ($this->regionDataProvider->isValidRegion($this->defaultRegion)) {
            return $this->defaultRegion;
        }

        return self::FALLBACK_REGION;
    }

    private function resolveLocale(): string
    {
        $regionCode = $this->getRegionCode();
        $locale = $this->localeProvider->getLocaleForRegion($regionCode);

        if ($locale && $this->localeProvider->isValidLocale($locale)) {
            return $locale;
        }

        if ($this->defaultLocale && $this->localeProvider->isValidLocale($this->defaultLocale)) {
            return $this->defaultLocale;
        }

        $locale = $this->localeProvider->getDefaultLocale();
        if ($locale && $this->localeProvider->isValidLocale($locale)) {
            return $locale;
        }

        return self::FALLBACK_LOCALE;
    }
}