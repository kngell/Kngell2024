<?php

declare(strict_types=1);

use Brick\Money\Money;

final class MoneyType implements TypeHandlerInterface
{
    public function __construct(private CurrencyCodeProviderInterface $currencyProvider)
    {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Money;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return $value?->getAmount();
    }

    public function normalizeForEntity(
        mixed $value,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        if ($value instanceof Money) {
            return $value;
        }

        $currencyCode = null;

        // 1️⃣ Try to get currency code directly
        if ($contextEntity instanceof Entity && method_exists($contextEntity, 'getCurrencyCodeIfExists')) {
            $currencyCode = $contextEntity->getCurrencyCodeIfExists();
        }

        // 2️⃣ If not, try via currencyId
        if ($currencyCode === null && $contextEntity instanceof Entity && method_exists($contextEntity, 'getCurrencyIdIfExists')) {
            $currencyId = $contextEntity->getCurrencyIdIfExists();
            if ($currencyId !== null) {
                $currencyCode = $this->currencyProvider->getCurrencyCode($currencyId);
            }
        }

        // 3️⃣ Fallback to system default
        if ($currencyCode === null) {
            $currencyCode = $this->currencyProvider->getSystemDefaultCurrencyCode();
        }

        try {
            return Money::of($value, $currencyCode);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                sprintf("Failed to instantiate Money for value '%s' with currency '%s'.", $value, $currencyCode),
                0,
                $e,
            );
        }
    }
}