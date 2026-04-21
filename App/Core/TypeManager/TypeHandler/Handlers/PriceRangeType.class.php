<?php

declare(strict_types=1);

class PriceRangeType implements TypeHandlerInterface
{
    public function __construct(private CurrencyCodeProviderInterface $currencyProvider)
    {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if ($property !== null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType && $propertyType->getName() === PriceRange::class) {
                return true;
            }
        }
        return $value instanceof PriceRange;
    }

    public function normalizeForEntity(
        mixed $rawValue,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        // Handle null
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Already a PriceRange object
        if ($rawValue instanceof PriceRange) {
            return $rawValue;
        }

        // Get currency from entity context or system default
        $currencyCode = $this->getCurrencyCode($contextEntity);

        // Handle array from form submission (has brackets with numeric values)
        if (is_array($rawValue) && isset($rawValue['brackets'])) {
            return PriceRange::fromDatabaseArray($rawValue, $currencyCode);
        }

        // Handle JSON string from database (numeric values)
        if (is_string($rawValue)) {
            $decoded = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['brackets'])) {
                return PriceRange::fromDatabaseArray($decoded, $currencyCode);
            }
        }

        return null;
    }

    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        if (!$entityValue instanceof PriceRange) {
            throw new InvalidArgumentException('Expected PriceRange instance');
        }

        return json_encode($entityValue->toArray());
    }

    private function getCurrencyCode(object $contextEntity): string
    {
        // Try to get currency from entity
        if ($contextEntity instanceof Category) {
            if (method_exists($contextEntity, 'getCurrencyCodeIfExists')) {
                $currencyCode = $contextEntity->getCurrencyCodeIfExists();
                if ($currencyCode) {
                    return $currencyCode;
                }
            }

            if (method_exists($contextEntity, 'getCurrencyIdIfExists')) {
                $currencyId = $contextEntity->getCurrencyIdIfExists();
                if ($currencyId) {
                    try {
                        return $this->currencyProvider->getCurrencyCode($currencyId);
                    } catch (Throwable $e) {
                        // Fall through to default
                    }
                }
            }
        }

        // Fallback to system default currency
        return $this->currencyProvider->getSystemDefaultCurrencyCode();
    }
}