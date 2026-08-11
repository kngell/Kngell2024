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
        private readonly PriceRangeType $priceRangeType,
        private readonly MoneyType $moneyType,
        private readonly WeightType $weightType,
        private readonly DimensionsType $dimensionsType,
        private readonly ObfuscatorManager $obfuscatorManager,
    ) {
    }

    public function getHandlerForValue(mixed $value, ?ReflectionProperty $property = null): TypeHandlerInterface
    {
        $this->initializeIfNeeded();

        if (ObfuscationUtils::isObfuscated($value)) {
            return $this->valueBasedHandlers['obfuscated'];
        }

        if ($property !== null && $this->hasObfuscateAttribute($property)) {
            return $this->valueBasedHandlers['obfuscated'];
        }

        // Priority 2: Handle null values
        if ($value === null) {
            return $this->valueBasedHandlers['null'];
        }

        // Priority 3: Handle empty string
        if ($value === '') {
            return $this->valueBasedHandlers['empty_string'];
        }

        // Priority 3: Type-based handlers (property type hints)
        if ($property !== null) {
            $typeBasedHandler = $this->getTypeBasedHandler($property, $value);
            if ($typeBasedHandler !== null) {
                return $typeBasedHandler;
            }
        }

        // Priority 4: Value-based handlers (actual value type)
        $valueBasedHandler = $this->getValueBasedHandler($value, $property);
        if ($valueBasedHandler !== null) {
            return $valueBasedHandler;
        }

        // Priority 5: Fallback to standard handler
        return $this->valueBasedHandlers['standard'];
    }

    /**
     * Get handler by type name (for explicit use).
     */
    public function getHandlerForType(string $type): ?TypeHandlerInterface
    {
        $this->initializeIfNeeded();
        return $this->typeBasedHandlers[$type] ?? $this->valueBasedHandlers[$type] ?? null;
    }

    /**
     * Get registered types for debugging.
     */
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

        if ($value === null) {
            return $this->valueBasedHandlers['null'];
        }

        if ($expectedType !== null) {
            $handler = $this->getHandlerForType($expectedType);
            if ($handler !== null && $handler->supports($value, null)) {
                return $handler;
            }
        }

        foreach ($this->valueBasedHandlers as $name => $handler) {
            if ($name === 'null' || $name === 'empty_string') {
                continue;
            }
            if ($handler->supports($value, null)) {
                return $handler;
            }
        }
        return $this->valueBasedHandlers['standard'];
    }

    public function getHandlerForClientInput(mixed $value, ReflectionProperty $property): TypeHandlerInterface
    {
        return $this->getHandlerForValue($value, $property);
    }

    public function getHandlerForDatabaseOutput(mixed $value, ReflectionProperty $property): TypeHandlerInterface
    {
        return $this->getHandlerForValue($value, $property);
    }

    private function getTypeBasedHandler(ReflectionProperty $property, mixed $value): ?TypeHandlerInterface
    {
        $propertyType = $property->getType();
        if ($propertyType === null) {
            return null;
        }

        // Empty string handling (convert to null for DB)
        if ($value === '' && $propertyType->allowsNull()) {
            return $this->valueBasedHandlers['empty_string'] ?? null;
        }

        // Handle named types
        if ($propertyType instanceof ReflectionNamedType) {
            return $this->handleNamedType($propertyType, $value);
        }

        // Handle union types
        if ($propertyType instanceof ReflectionUnionType) {
            return $this->handleUnionType($propertyType, $value);
        }

        // Handle intersection types
        if ($propertyType instanceof ReflectionIntersectionType) {
            return $this->handleIntersectionType($propertyType, $value);
        }

        return null;
    }

    private function handleNamedType(ReflectionNamedType $type, mixed $value): ?TypeHandlerInterface
    {
        $typeName = $type->getName();

        // Enums - convert string ↔ enum
        if (enum_exists($typeName)) {
            return $this->typeBasedHandlers['enum'] ?? null;
        }

        // Registered class types (Money, Weight, Dimensions, PriceRange, etc.)
        if (isset($this->typeBasedHandlers[$typeName])) {
            return $this->typeBasedHandlers[$typeName];
        }

        // Built-in scalar types
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
            // Skip null and empty_string handlers (handled separately)
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
            'array' => new ArrayType(),
            'datetime' => new DateTimeType(),
            'uuid' => new UuidType(),
            'obfuscated' => new ObfuscatedType($this->obfuscatorManager),  // ← ADD THIS
            'standard' => new StandardType(),
        ];
    }

    private function initializeTypeBasedHandlers(): void
    {
        $this->typeBasedHandlers = [
            // Scalar types
            'bool' => new BooleanType(),
            'boolean' => new BooleanType(),
            'int' => new IntegerType(),
            'integer' => new IntegerType(),
            'float' => new FloatType(),
            'double' => new FloatType(),
            'string' => new StringType(),
            'array' => new ArrayType(),

            // Class types
            Entity::class => new EntityType(),
            DateTime::class => new DateTimeType(),
            DateTimeImmutable::class => new DateTimeType(),
            DateTimeInterface::class => new DateTimeType(),
            UuidInterface::class => new UuidType(),

            // Value object types
            Money::class => $this->moneyType,
            Weight::class => $this->weightType,
            Dimensions::class => $this->dimensionsType,
            PriceRange::class => $this->priceRangeType,

            // Special handlers
            'enum' => new EnumType(),
            'object' => new ObjectType(),
            // Note: ObfuscatedType is NOT here because it's detected by attribute,
            // not by type name. It's in valueBasedHandlers and detected via hasObfuscateAttribute()
        ];
    }

    private function hasObfuscateAttribute(ReflectionProperty $property): bool
    {
        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            if ($format->obfuscate === true) {
                return true;
            }
        }
        return false;
    }
}