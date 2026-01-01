<?php

declare(strict_types=1);

final class DefaultFallbackSymbolProvider implements FallbackSymbolProviderInterface
{
    private const SYMBOL_MAP = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'CNY' => '¥',
        'CNH' => '¥',
        'HKD' => 'HK$',
        'SGD' => 'S$',
        'NZD' => 'NZ$',
        'INR' => '₹',
        'RUB' => '₽',
        'BRL' => 'R$',
        'KRW' => '₩',
        'MXN' => '$',
        'CHF' => 'CHF',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
        'PLN' => 'zł',
        'TRY' => '₺',
        'THB' => '฿',
        'IDR' => 'Rp',
        'PHP' => '₱',
        'MYR' => 'RM',
        'ZAR' => 'R',
        'ILS' => '₪',
        'AED' => 'د.إ',
        'SAR' => 'ر.س',
        'QAR' => 'ر.ق',
        'KWD' => 'د.ك',
        'EGP' => '£',
        'PKR' => '₨',
        'BDT' => '৳',
        'VND' => '₫',
    ];

    public function getFallbackSymbol(string $currencyCode): string
    {
        $upperCode = strtoupper($currencyCode);

        if (isset(self::SYMBOL_MAP[$upperCode])) {
            return self::SYMBOL_MAP[$upperCode];
        }

        // Try to detect based on country/region patterns
        return $this->detectSymbolFromPattern($upperCode);
    }

    public function getAllKnownSymbols(): array
    {
        return self::SYMBOL_MAP;
    }

    private function detectSymbolFromPattern(string $currencyCode): string
    {
        // Pattern matching logic
        $patterns = [
            '/^X(A[E-FU]|C[D-A]|P[T]|U)/' => '$', // Special drawing rights, precious metals
            '/^US/' => '$',
            '/^EU/' => '€',
            '/^GB/' => '£',
            '/^AU/' => 'A$',
            '/^CA/' => 'C$',
            '/^NZ/' => 'NZ$',
            '/^SG/' => 'S$',
            '/^HK/' => 'HK$',
        ];

        foreach ($patterns as $pattern => $symbol) {
            if (preg_match($pattern, $currencyCode)) {
                return $symbol;
            }
        }

        return $currencyCode;
    }
}