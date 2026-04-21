<?php

declare(strict_types=1);

use Brick\Money\Money;
use Ramsey\Uuid\UuidInterface;

final class TypePresenterFactory implements TypePresenterFactoryInterface
{
    private array $valueBasedPresenters = [];
    private array $typeBasedPresenters = [];
    private bool $isInitialized = false;

    public function __construct(
        private TranslatorServiceInterface $translator,
        private RegionContextInterface $regionContext,
        private ObfuscatorManager $obfuscatorManager,
    ) {
    }

    public function getPresenterForValue(mixed $value, ?ReflectionProperty $property = null): TypePresenterInterface
    {
        $this->initializeIfNeeded();

        if ($property !== null) {
            $formatAttributes = $property->getAttributes(DisplayFormat::class);
            foreach ($formatAttributes as $attribute) {
                $format = $attribute->newInstance();

                // Check for obfuscation
                if ($format->obfuscate === true) {
                    return $this->valueBasedPresenters['obfuscated']; // Updated key
                }
            }
        }

        if ($property !== null) {
            $formatAttributes = $property->getAttributes(DisplayFormat::class);
            foreach ($formatAttributes as $attribute) {
                $format = $attribute->newInstance();

                // Check for obfuscation
                if ($format->obfuscate === true) {
                    return $this->valueBasedPresenters['obfuscated_id'];
                }

                // Add other format-based presenter selection here
                // e.g., date formatting, number formatting, etc.
                if ($format->style === 'date' || $format->dateStyle !== null) {
                    // You could have specialized date presenters
                }
            }
        }

        // STRATEGY 1: Check property type
        if ($property !== null) {
            $typeBasedPresenter = $this->getTypeBasedPresenter($property, $value);
            if ($typeBasedPresenter !== null) {
                return $typeBasedPresenter;
            }
        }

        // STRATEGY 1.5: Special handling for collections/arrays of objects
        if ($this->isObjectCollection($value)) {
            return $this->valueBasedPresenters['collection'];
        }

        // STRATEGY 2: Try VALUE-BASED matching
        $valueBasedPresenter = $this->getValueBasedPresenter($value, $property);
        if ($valueBasedPresenter !== null) {
            return $valueBasedPresenter;
        }

        // STRATEGY 3: Fallback
        return $this->valueBasedPresenters['standard'];
    }

    public function getPresenterForType(string $type): ?TypePresenterInterface
    {
        $this->initializeIfNeeded();
        return $this->typeBasedPresenters[$type] ?? $this->valueBasedPresenters[$type] ?? null;
    }

    public function displayValue(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        $presenter = $this->getPresenterForValue($value, $property);
        return $presenter->display($value, $property, $regionContext ?? $this->regionContext);
    }

    private function isObjectCollection(mixed $value): bool
    {
        if ($value instanceof CollectionInterface) {
            return true;
        }

        if (!is_array($value) || empty($value)) {
            return false;
        }

        $firstItem = reset($value);
        return is_object($firstItem);
    }

    private function getTypeBasedPresenter(ReflectionProperty $property, mixed $value): ?TypePresenterInterface
    {
        $propertyType = $property->getType();
        if ($propertyType === null) {
            return null;
        }

        if ($propertyType instanceof ReflectionUnionType) {
            return $this->handleUnionType($propertyType, $value);
        }

        if ($propertyType instanceof ReflectionIntersectionType) {
            return $this->handleIntersectionType($propertyType, $value);
        }

        if ($propertyType instanceof ReflectionNamedType) {
            return $this->handleNamedType($propertyType, $value);
        }

        return null;
    }

    private function handleNamedType(ReflectionNamedType $type, mixed $value): ?TypePresenterInterface
    {
        $typeName = $type->getName();

        // Handle arrays - check if it's an array of objects
        if ($typeName === 'array' && $this->isObjectCollection($value)) {
            return $this->valueBasedPresenters['collection'];
        }
        if ($typeName === 'array' && $value instanceof Entity) {
            return null;
        }

        // Handle enums
        if (enum_exists($typeName)) {
            return $this->typeBasedPresenters['enum'] ?? null;
        }

        // Handle registered class types
        if (isset($this->typeBasedPresenters[$typeName])) {
            return $this->typeBasedPresenters[$typeName];
        }

        // Handle built-in scalar types
        $scalarType = $this->normalizeScalarType($typeName);
        if (isset($this->typeBasedPresenters[$scalarType])) {
            return $this->typeBasedPresenters[$scalarType];
        }

        return null;
    }

    private function handleUnionType(ReflectionUnionType $unionType, mixed $value): ?TypePresenterInterface
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof ReflectionNamedType) {
                $presenter = $this->handleNamedType($type, $value);
                if ($presenter !== null) {
                    return $presenter;
                }
            }
        }
        return null;
    }

    private function handleIntersectionType(ReflectionIntersectionType $intersectionType, mixed $value): ?TypePresenterInterface
    {
        return null;
    }

    private function getValueBasedPresenter(mixed $value, ?ReflectionProperty $property): ?TypePresenterInterface
    {
        foreach ($this->valueBasedPresenters as $name => $presenter) {
            if ($name === 'null') {
                continue;
            }

            try {
                if ($presenter->supports($value, $property)) {
                    return $presenter;
                }
            } catch (Throwable $e) {
                error_log("Presenter {$name} failed supports check: " . $e->getMessage());
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

        $this->initializeValueBasedPresenters();
        $this->initializeTypeBasedPresenters();

        $this->isInitialized = true;
    }

    private function initializeValueBasedPresenters(): void
    {
        $this->valueBasedPresenters = [
            'null' => new NullPresenter(),
            'array' => new ArrayPresenter($this->translator),
            'collection' => new CollectionPresenter($this, $this->translator),
            'datetime' => new DateTimePresenter($this->regionContext),
            'money' => App::diGet(MoneyPresenter::class),
            'obfuscated' => new ObfuscatedPresenter(
                $this->obfuscatorManager,
            ),
            'standard' => new StandardPresenter(),
        ];
    }

    private function initializeTypeBasedPresenters(): void
    {
        $this->typeBasedPresenters = [
            // Scalar types
            'bool' => new BooleanPresenter($this->translator),
            'int' => new StandardPresenter(),
            'float' => new FloatPresenter($this->regionContext),
            'string' => new StandardPresenter(),

            // Class types
            DateTime::class => new DateTimePresenter($this->regionContext),
            DateTimeImmutable::class => new DateTimePresenter($this->regionContext),
            Money::class => App::diGet(MoneyPresenter::class),
            Weight::class => new WeightPresenter($this->regionContext, $this->translator),
            Dimensions::class => new DimensionsPresenter($this->regionContext, $this->translator),
            UuidInterface::class => new UuidPresenter(),

            // Add PriceRange presenter
            PriceRange::class => new PriceRangeFormPresenter(),

            // Special types
            'enum' => new EnumPresenter($this->translator),
            'object' => new ObjectPresenter($this),
            'array' => new ArrayPresenter($this->translator),
            'collection' => new CollectionPresenter($this, $this->translator),
        ];
    }
}