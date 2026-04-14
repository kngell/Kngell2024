<?php

declare(strict_types=1);

final class CurrencyService implements CurrencyLookupInterface
{
    public function __construct(
        private CurrencyModel $currencyModel,
    ) {
    }

    public function getActiveCurrencies(): array
    {
        try {
            $currencies = $this->currencyModel->findActive();

            $options = ['' => '-- Select Currency --'];
            foreach ($currencies as $currency) {
                if ($currency instanceof Currency) {
                    $options[$currency->getId()] = $this->formatCurrencyLabel($currency);
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('CurrencyService: Failed to load currencies - ' . $e->getMessage());
            return $this->getDefaultCurrencies();
        }
    }

    public function findCurrencyById(int $currencyId): ?object
    {
        try {
            return $this->currencyModel->find($currencyId)->asClass();
        } catch (QueryResultException $e) {
            error_log("CurrencyService: Failed to get currency ID {$currencyId} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get default currency ID (could be from user preferences, config, or first active).
     */
    public function getDefaultCurrencyId(): int
    {
        try {
            // You could modify this to get from user preferences or app config
            $default = $this->currencyModel->findByCode('EUR');
            return $default ? $default->getId() : 1; // Fallback to ID 1
        } catch (QueryResultException $e) {
            error_log('CurrencyService: Failed to get default currency - ' . $e->getMessage());
            return 1; // Fallback to first currency
        }
    }

    /**
     * Get currency by ID.
     */
    public function getCurrencyById(int $currencyId): ?Currency
    {
        try {
            return $this->currencyModel->find($currencyId)->asClass();
        } catch (QueryResultException $e) {
            error_log("CurrencyService: Failed to get currency ID {$currencyId} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get currency code by ID (useful for display).
     */
    public function getCurrencyCodeById(int $currencyId): string
    {
        $currency = $this->getCurrencyById($currencyId);
        return $currency ? $currency->getCurrencyCode() : 'EUR';
    }

    private function formatCurrencyLabel(Currency $currency): string
    {
        $symbol = $currency->getSymbol();
        $name = $currency->getCurrencyName();
        $code = $currency->getCurrencyCode();

        if (!empty($symbol)) {
            return "{$symbol} {$name} ({$code})";
        }

        return "{$name} ({$code})";
    }

    private function getDefaultCurrencies(): array
    {
        // Fallback hardcoded currencies with IDs
        return [
            '' => '-- Select Currency --',
            '1' => '€ Euro (EUR)',
            '2' => '$ US Dollar (USD)',
            '3' => '£ British Pound (GBP)',
        ];
    }
}