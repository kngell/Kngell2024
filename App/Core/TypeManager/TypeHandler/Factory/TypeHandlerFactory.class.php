<?php

declare(strict_types=1);

use Brick\Money\Money;
use Ramsey\Uuid\UuidInterface;

final class TypeHandlerFactory
{
    /** @var array<string, TypeHandlerInterface> */
    private array $valueBasedHandlers = [];

    /** @var array<string, TypeHandlerInterface> */
    private array $typeBasedHandlers = [];

    private bool $isInitialized = false;

    public function __construct(
    ) {
    }

    public function getHandlerForValue(mixed $value, ?ReflectionProperty $property = null): TypeHandlerInterface
    {
        $this->initializeIfNeeded();

        if ($value === null) {
            return $this->valueBasedHandlers['null'];
        }

        if ($value === '') {
            return $this->valueBasedHandlers['empty_string'];
        }

        if ($property !== null) {
            $attributes = $property->getAttributes(EntityFieldId::class);
            foreach ($attributes as $attribute) {
                $fieldId = $attribute->newInstance();
                if ($fieldId->shouldObfuscate()) {
                    return $this->valueBasedHandlers['obfuscator'];
                }
            }
        }

        if ($property !== null) {
            $typeBasedHandler = $this->getTypeBasedHandler($property, $value);
            if ($typeBasedHandler !== null) {
                return $typeBasedHandler;
            }
        }

        $valueBasedHandler = $this->getValueBasedHandler($value, $property);
        if ($valueBasedHandler !== null) {
            return $valueBasedHandler;
        }

        return $this->valueBasedHandlers['standard'];
    }

    public function getHandlerForType(string $type): ?TypeHandlerInterface
    {
        $this->initializeIfNeeded();
        return $this->typeBasedHandlers[$type] ?? $this->valueBasedHandlers[$type] ?? null;
    }

    public function getRegisteredTypes(): array
    {
        $this->initializeIfNeeded();
        return [
            'value_based' => array_keys($this->valueBasedHandlers),
            'type_based' => array_keys($this->typeBasedHandlers),
        ];
    }

    /**
     * Optimized version for query building that doesn't need entity context.
     */
    public function getHandlerForQueryValue(mixed $value, ?string $expectedType = null): TypeHandlerInterface
    {
        $this->initializeIfNeeded();

        // Handle NULL values
        if ($value === null) {
            return $this->valueBasedHandlers['null'];
        }

        // Try type-based matching if expected type is provided
        if ($expectedType !== null) {
            $handler = $this->getHandlerForType($expectedType);
            if ($handler !== null && $handler->supports($value, null)) {
                return $handler;
            }
        }

        // Fall back to value-based detection
        foreach ($this->valueBasedHandlers as $name => $handler) {
            if ($name === 'null') {
                continue;
            }

            if ($handler->supports($value, null)) {
                return $handler;
            }
        }
        return $this->valueBasedHandlers['standard'];
    }

    private function getTypeBasedHandler(ReflectionProperty $property, mixed $value): ?TypeHandlerInterface
    {
        $propertyType = $property->getType();
        if ($propertyType === null) {
            return null;
        }

        // 🟢 FIX: Check for empty string FIRST - highest priority
        if ($value === '' && $propertyType->allowsNull()) {
            // Empty string should be handled by EmptyStringType
            return $this->valueBasedHandlers['empty_string'] ?? null;
        }

        // Check for Money type with HIGH priority
        if ($propertyType instanceof ReflectionNamedType && $propertyType->getName() === Money::class) {
            return $this->typeBasedHandlers[Money::class] ?? null;
        }

        // Handle union types
        if ($propertyType instanceof ReflectionUnionType) {
            // First check if any union type is Money
            foreach ($propertyType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType && $type->getName() === Money::class) {
                    return $this->typeBasedHandlers[Money::class] ?? null;
                }
            }
            return $this->handleUnionType($propertyType, $value);
        }

        // Handle intersection types
        if ($propertyType instanceof ReflectionIntersectionType) {
            return $this->handleIntersectionType($propertyType, $value);
        }

        // Handle named types
        if ($propertyType instanceof ReflectionNamedType) {
            return $this->handleNamedType($propertyType, $value);
        }

        return null;
    }

    private function handleNamedType(ReflectionNamedType $type, mixed $value): ?TypeHandlerInterface
    {
        $typeName = $type->getName();

        // Handle enums - HIGHEST PRIORITY
        if (enum_exists($typeName)) {
            return $this->typeBasedHandlers['enum'] ?? null;
        }

        // Handle registered class types
        if (isset($this->typeBasedHandlers[$typeName])) {
            return $this->typeBasedHandlers[$typeName];
        }

        // Handle built-in scalar types
        $scalarType = $this->normalizeScalarType($typeName);
        if (isset($this->typeBasedHandlers[$scalarType])) {
            return $this->typeBasedHandlers[$scalarType];
        }

        return null;
    }

    private function handleUnionType(ReflectionUnionType $unionType, mixed $value): ?TypeHandlerInterface
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof ReflectionNamedType) {
                $handler = $this->handleNamedType($type, $value);
                if ($handler !== null && $handler->supports($value, null)) {
                    return $handler;
                }
            }
        }

        return null;
    }

    private function handleIntersectionType(ReflectionIntersectionType $intersectionType, mixed $value): ?TypeHandlerInterface
    {
        // For intersection types, we need all types to be satisfied
        // This is complex, so fall back to value-based detection
        return null;
    }

    private function getValueBasedHandler(mixed $value, ?ReflectionProperty $property): ?TypeHandlerInterface
    {
        foreach ($this->valueBasedHandlers as $name => $handler) {
            if ($name === 'null' || $name === 'empty_string') {
                continue;
            }

            try {
                if ($handler->supports($value, $property)) {
                    return $handler;
                }
            } catch (Throwable $e) {
                error_log("Handler {$name} failed supports check: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    private function normalizeScalarType(string $type): string
    {
        return match ($type) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => $type,
        };
    }

    private function initializeIfNeeded(): void
    {
        if ($this->isInitialized) {
            return;
        }

        $this->initializeValueBasedHandlers();
        $this->initializeTypeBasedHandlers();

        $this->isInitialized = true;
    }

    private function initializeValueBasedHandlers(): void
    {
        $this->valueBasedHandlers = [
            'null' => new NullType(),
            'empty_string' => new EmptyStringType(),
            'binary' => new BinaryType(),
            'hex_literal' => new HexLiteralType(),
            'array' => new ArrayType(),
            'entity' => new EntityType(),
            'datetime' => new DateTimeType(),
            'uuid' => new UuidType(),
            'standard' => new StandardType(),
        ];
    }

    private function initializeTypeBasedHandlers(): void
    {
        $this->typeBasedHandlers = [
            // Scalar types (with both names for compatibility)
            'bool' => new StandardType(),
            'boolean' => new StandardType(),
            'int' => new StandardType(),
            'integer' => new StandardType(),
            'float' => new StandardType(),
            'double' => new StandardType(),
            'string' => new StandardType(),
            'array' => new ArrayType(),

            // Class types
            Entity::class => new EntityType(),
            DateTime::class => new DateTimeType(),
            DateTimeImmutable::class => new DateTimeType(),
            DateTimeInterface::class => new DateTimeType(),

            // Your custom types
            PriceRange::class => new PriceRangeType(),
            Weight::class => new WeightType(),
            Dimensions::class => new DimensionsType(),
            UuidInterface::class => new UuidType(),

            // Special handlers
            'binary' => new BinaryType(),
            'hex_literal' => new HexLiteralType(),
            'enum' => new EnumType(),
            'object' => new ObjectType(),
        ];

        // Add Money type if available
        if (class_exists(Money::class)) {
            $this->typeBasedHandlers[Money::class] = App::diGet(MoneyType::class);
        }
    }
}