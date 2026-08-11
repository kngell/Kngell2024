<?php

declare(strict_types=1);

final class LazyCurrencyCodeProvider implements CurrencyCodeProviderInterface
{
    private ?CurrencyCodeProviderInterface $resolved = null;

    /**
     * @param Closure(): CurrencyCodeProviderInterface $factory
     */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    public function getCurrencyCode(int $currencyId): string
    {
        return $this->resolve()->getCurrencyCode($currencyId);
    }

    public function getSystemDefaultCurrencyCode(?string $regionCode = null): string
    {
        return $this->resolve()->getSystemDefaultCurrencyCode($regionCode);
    }

    public function isValidCurrency(string $currencyCode): bool
    {
        return $this->resolve()->isValidCurrency($currencyCode);
    }

    public function getCurrencySymbol(string $currencyCode): ?string
    {
        return $this->resolve()->getCurrencySymbol($currencyCode);
    }

    public function getCurrencyById(int $currencyId): ?Currency
    {
        return $this->resolve()->getCurrencyById($currencyId);
    }

    public function getCurrencyByCode(string $currencyCode): ?Currency
    {
        return $this->resolve()->getCurrencyByCode($currencyCode);
    }

    private function resolve(): CurrencyCodeProviderInterface
    {
        return $this->resolved ??= ($this->factory)();
    }
}