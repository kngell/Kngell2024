<?php

declare(strict_types=1);

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

class GeoIPRegionContext implements RegionContextResolutionInterface
{
    private Reader $reader;

    public function __construct(
        private Request $request,
    ) {
        $dbPath = STORAGE . 'database/geoip/GeoLite2-Country.mmdb';

        if (!file_exists($dbPath)) {
            throw new RuntimeException("GeoIP2 database not found at: {$dbPath}");
        }

        $this->reader = new Reader($dbPath);
    }

    public function resolveRegion(): ?string
    {
        $ip = $this->request->getClientIp();

        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            // Optional: fallback for local dev/testing
            return 'EU';
        }

        return $this->lookupRegionFromIp($ip);
    }

    public function getPriority(): int
    {
        return 60;
    }

    private function lookupRegionFromIp(string $ip): ?string
    {
        try {
            $record = $this->reader->country($ip);
            $countryCode = $record->country->isoCode ?? null;

            if (!$countryCode) {
                return null;
            }
            // Map country codes to your internal region system
            return match ($countryCode) {
                'US', 'CA', 'MX' => 'NA', // North America
                'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'PT', 'PL', 'IE' => 'EU', // Europe
                'JP', 'CN', 'KR', 'SG', 'HK' => 'ASIA',
                'AU', 'NZ' => 'OCEANIA',
                default => 'INTL', // fallback region
            };
        } catch (AddressNotFoundException $e) {
            // IP not found in database
            return null;
        } catch (Throwable $e) {
            // Any other unexpected error
            return null;
        }
    }
}