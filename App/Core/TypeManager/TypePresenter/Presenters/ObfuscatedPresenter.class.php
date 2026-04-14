<?php

// ObfuscatedPresenter.php
declare(strict_types=1);

final class ObfuscatedPresenter implements TypePresenterInterface
{
    public function __construct(
        private ObfuscatorManager $obfuscatorManager,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if ($property === null) {
            return false;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            if ($format->obfuscate === true) {
                return true;
            }
        }

        return false;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $format = $this->getDisplayFormat($property);

        // Handle null
        if ($value === null) {
            return $format?->nullPlaceholder ?? '';
        }

        // If obfuscation is not enabled, just stringify
        if ($format?->obfuscate !== true) {
            return $this->applyPrefixSuffix((string) $value, $format);
        }

        $strategy = $format->obfuscationStrategy ?? 'hashid';
        $obfuscator = $this->obfuscatorManager->strategy($strategy);

        // Obfuscate the value
        if (is_int($value)) {
            $obfuscated = $obfuscator->obfuscate($value);
            return $this->applyPrefixSuffix($obfuscated, $format);
        }

        // If it's already a string, verify it's valid
        if (is_string($value)) {
            if ($obfuscator->deobfuscate($value) !== null) {
                return $this->applyPrefixSuffix($value, $format);
            }
        }

        return $this->applyPrefixSuffix((string) $value, $format);
    }

    /**
     * Normalize a value for entity (form → entity)
     * This matches the TypeHandler pattern.
     */
    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        $format = $this->getDisplayFormat($property);

        // If obfuscation is not enabled, return as-is
        if ($format?->obfuscate !== true) {
            return $value;
        }

        // Handle null
        if ($value === null) {
            return null;
        }

        // Only process strings (obfuscated values come as strings from forms)
        if (!is_string($value)) {
            return $value;
        }

        // Remove prefix if present
        $cleanValue = $format->prefix ? ltrim($value, $format->prefix) : $value;

        $strategy = $format->obfuscationStrategy ?? 'hashid';
        $obfuscator = $this->obfuscatorManager->strategy($strategy);

        // Deobfuscate
        $deobfuscated = $obfuscator->deobfuscate($cleanValue);

        if ($deobfuscated === null) {
            // If deobfuscation fails, log warning but return original
            error_log(sprintf(
                'Failed to deobfuscate value "%s" for property %s::%s',
                $value,
                $property->getDeclaringClass()->getName(),
                $property->getName(),
            ));

            return $value;
        }

        // Get the expected type from the property
        $propertyType = $property->getType();
        $expectedType = $propertyType instanceof ReflectionNamedType ? $propertyType->getName() : 'mixed';

        if ($propertyType->allowsNull() && $deobfuscated === 0) {
            return null;
        }
        // Cast to expected type
        return match($expectedType) {
            'int', 'integer' => (int) $deobfuscated,
            'string' => (string) $deobfuscated,
            default => $deobfuscated,
        };
    }

    /**
     * Deobfuscate a value (convenience method).
     */
    public function deobfuscate(string $value, ReflectionProperty $property): mixed
    {
        return $this->normalizeForEntity($value, $property, $property->getDeclaringClass()->newInstanceWithoutConstructor());
    }

    private function getDisplayFormat(?ReflectionProperty $property): ?DisplayFormat
    {
        if ($property === null) {
            return null;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    private function applyPrefixSuffix(string $value, ?DisplayFormat $format): string
    {
        if ($format === null) {
            return $value;
        }

        $result = $value;
        if ($format->prefix !== null) {
            $result = $format->prefix . $result;
        }
        if ($format->suffix !== null) {
            $result = $result . $format->suffix;
        }
        return $result;
    }
}