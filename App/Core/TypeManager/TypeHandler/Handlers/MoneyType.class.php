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
        // If we have property context and it expects Money
        if ($property !== null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType && $propertyType->getName() === Money::class) {
                return true; // Handle any value for Money properties
            }
        }

        // Otherwise, only support actual Money objects
        return $value instanceof Money;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        if ($value instanceof Money) {
            return $value->getAmount()->__toString();
        }

        if ($value instanceof Brick\Math\BigDecimal) {
            return $value->__toString(); // It is already the amount
        }

        return $value;
    }

    public function normalizeForEntity(
        mixed $value,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        if ($value instanceof Money) {
            return $value;
        }

        if ($value === '' || $value === null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType && $propertyType->allowsNull()) {
                return null;
            }
            if ($property->hasDefaultValue()) {
                return $property->getDefaultValue();
            }
            throw new InvalidArgumentException(
                sprintf(
                    "Property '%s' in %s is not nullable and has no default value, but received an empty string.",
                    $property->getName(),
                    $property->getDeclaringClass()->getName(),
                ),
            );
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