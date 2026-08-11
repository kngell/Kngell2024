<?php

declare(strict_types=1);

use Brick\Math\Exception\MathException;
use Brick\Money\Context\AutoContext;
use Brick\Money\Currency;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Money;

final class MoneyManager
{
    private Currency $currency;
    private array $currencyCache = [];

    public function __construct(
        private readonly RegionContextInterface $regionContext,
        private readonly CurrencyCodeProviderInterface $currencyCodeProvider,
        ?string $currencyCode = null,
        ?string $regionCode = null,
    ) {
        if ($regionCode !== null) {
            $currencyCode = $this->currencyCodeProvider->getSystemDefaultCurrencyCode($regionCode);
        } elseif ($currencyCode === null) {
            $currencyCode = $this->regionContext->getCurrencyCode();
        }

        if (!$this->currencyCodeProvider->isValidCurrency($currencyCode)) {
            throw new InvalidArgumentException(
                sprintf('Invalid currency code: "%s"', $currencyCode),
            );
        }

        $this->currency = Currency::of($currencyCode);
    }

    public function createMoney(int|float|string|null $amount, ?string $currencyCode = null): Money
    {
        if ($amount === null) {
            $amount = '0';
        }

        if (is_float($amount)) {
            $amount = $this->floatToString($amount);
        }

        $currency = $currencyCode
            ? $this->getCurrencyFromCode($currencyCode)
            : $this->currency;

        return Money::of($amount, $currency, new AutoContext());
    }

    public function sumMoneyArray(array $monies): Money
    {
        if (empty($monies)) {
            return $this->zero();
        }
        $total = $monies[0];

        for ($i = 1; $i < count($monies); $i++) {
            $total = $total->plus($monies[$i]->toRational());
        }

        return $total;
    }

    public function createMoneyFromCurrencyId(int|float|string|null $amount, int $currencyId): Money
    {
        $currencyCode = $this->currencyCodeProvider->getCurrencyCode($currencyId);
        return $this->createMoney($amount, $currencyCode);
    }

    public function format(Money $money): string
    {
        $amount = (float) $this->getAmount($money);
        $currencyCode = $money->getCurrency()->getCurrencyCode();
        return $this->regionContext->formatCurrency($amount, $currencyCode);
    }

    public function formatAmount(int|float|string|null $amount, ?string $currencyCode = null): string
    {
        $money = $this->createMoney($amount, $currencyCode);
        return $this->format($money);
    }

    public function getAmount(Money $money): string
    {
        return (string) $money->getAmount();
    }

    public function getMinorAmount(Money $money): int
    {
        return $money->getMinorAmount()->toInt();
    }

    public function sum(Money ...$monies): Money
    {
        if (empty($monies)) {
            return $this->zero();
        }

        $currency = $monies[0]->getCurrency();
        $currencyCode = $currency->getCurrencyCode();

        foreach ($monies as $money) {
            if (!$money->getCurrency()->is($currency)) {
                throw new CurrencyConversionException(
                    sprintf(
                        'Cannot sum money with different currencies. Found: %s, Expected: %s',
                        $money->getCurrency()->getCurrencyCode(),
                        $currencyCode,
                    ),
                    $money->getCurrency()->getCurrencyCode(),
                    $currencyCode,
                );
            }
        }

        $total = $monies[0];
        for ($i = 1; $i < count($monies); $i++) {
            $total = $total->plus($monies[$i]);
        }

        return $total;
    }

    public function sumItems(array $items, string $priceKey = 'price', string $quantityKey = 'quantity', ?string $currencyCode = null): Money
    {
        $total = $this->zero($currencyCode);

        foreach ($items as $item) {
            $price = $item[$priceKey] ?? '0';
            $quantity = $item[$quantityKey] ?? 1;

            try {
                $money = $this->createMoney($price, $currencyCode);
                $total = $total->plus($money->multipliedBy($quantity));
            } catch (MathException $e) {
                throw new RuntimeException(
                    sprintf('Error processing item with price: %s', $price),
                    0,
                    $e,
                );
            }
        }

        return $total;
    }

    public function zero(?string $currencyCode = null): Money
    {
        $currency = $currencyCode
            ? $this->getCurrencyFromCode($currencyCode)
            : $this->currency;

        return Money::zero($currency);
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency->getCurrencyCode();
    }

    public function getCurrencySymbol(): ?string
    {
        return $this->currencyCodeProvider->getCurrencySymbol($this->getCurrencyCode());
    }

    public function getNumberFormat(): array
    {
        return $this->regionContext->getNumberFormat();
    }

    public function parse(string $amount, ?string $currencyCode = null): Money
    {
        $numberFormat = $this->regionContext->getNumberFormat();

        $cleaned = preg_replace('/[^0-9,.]/', '', $amount);

        if (isset($numberFormat['decimal_separator']) && $numberFormat['decimal_separator'] === ',') {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        $thousandsSep = $numberFormat['thousands_separator'] ?? ',';
        if ($thousandsSep !== '' && $thousandsSep !== '.') {
            $cleaned = str_replace($thousandsSep, '', $cleaned);
        }

        try {
            return $this->createMoney($cleaned, $currencyCode);
        } catch (MathException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid amount format: %s', $amount),
                0,
                $e,
            );
        }
    }

    public function compare(Money $first, Money $second): int
    {
        return $first->compareTo($second);
    }

    public function equals(Money $first, Money $second): bool
    {
        return $first->isEqualTo($second);
    }

    public function isZero(Money $money): bool
    {
        return $money->isZero();
    }

    public function isPositive(Money $money): bool
    {
        return $money->isPositive();
    }

    public function isNegative(Money $money): bool
    {
        return $money->isNegative();
    }

    private function floatToString(float $value): string
    {
        $string = (string) $value;

        if (strpos($string, 'E') !== false || strpos($string, 'e') !== false) {
            $string = number_format($value, 10, '.', '');
        }

        return rtrim(rtrim($string, '0'), '.');
    }

    private function getCurrencyFromCode(string $currencyCode): Currency
    {
        $key = strtoupper($currencyCode);

        if (!isset($this->currencyCache[$key])) {
            if (!$this->currencyCodeProvider->isValidCurrency($key)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid currency code: "%s"', $key),
                );
            }
            $this->currencyCache[$key] = Currency::of($key);
        }

        return $this->currencyCache[$key];
    }
}