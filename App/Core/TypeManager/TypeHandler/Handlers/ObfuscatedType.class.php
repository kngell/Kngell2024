<?php

declare(strict_types=1);

final class ObfuscatedType implements TypeHandlerInterface
{
    public function __construct(
        private ObfuscatorManager $obfuscatorManager,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        // Support any value that is explicitly determined to be obfuscated
        if (ObfuscationUtils::isObfuscated($value)) {
            return true;
        }

        // Or a property flagged with the DisplayFormat obfuscate attribute
        if ($property !== null) {
            $format = $this->getDisplayFormat($property);
            if ($format?->obfuscate === true) {
                return true;
            }
        }

        return false;
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, ?Entity $entity): mixed
    {
        if (!ObfuscationUtils::isObfuscated($value)) {
            return $value;
        }

        $format = $this->getDisplayFormat($property);

        $strategyName = $format?->obfuscationStrategy
            ?? ObfuscationUtils::getStrategyFromPrefix((string) $value);
        $cleanValue = ObfuscationUtils::stripPrefix((string) $value);

        $strategy = $this->obfuscatorManager->strategy($strategyName);
        $rawId = $strategy->deobfuscate($cleanValue);

        if ($rawId === null) {
            error_log(sprintf(
                'Failed to deobfuscate value "%s" using strategy "%s" for property %s::%s',
                $value,
                $strategyName,
                $property->getDeclaringClass()->getName(),
                $property->getName(),
            ));
            return null;
        }
        $propertyType = $property->getType();
        if (!$propertyType instanceof ReflectionNamedType) {
            return $rawId;
        }

        $expectedType = $propertyType->getName();

        if ($propertyType->allowsNull() && ($rawId === 0 || $rawId === '0')) {
            return null;
        }

        return match($expectedType) {
            'int', 'integer' => (int) $rawId,
            'float', 'double' => (float) $rawId,
            'string' => (string) $rawId,
            'bool', 'boolean' => (bool) $rawId,
            default => $rawId,
        };
    }

    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        return $entityValue;
    }

    private function getDisplayFormat(ReflectionProperty $property): ?DisplayFormat
    {
        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}