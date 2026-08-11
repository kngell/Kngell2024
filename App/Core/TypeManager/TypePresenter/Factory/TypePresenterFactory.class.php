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
        private ObfuscatorManager $obfuscatorManager,
        private RegionContextInterface $regionContext,
        private MoneyPresenter $moneyPresenter,
    ) {
    }

    public function getPresenterForValue(mixed $value, ?ReflectionProperty $property = null): TypePresenterInterface
    {
        $this->initializeIfNeeded();
        if ($property !== null && $this->hasObfuscateAttribute($property)) {
            return $this->valueBasedPresenters['obfuscated'];
        }
        if ($property !== null) {
            $typeBasedPresenter = $this->getTypeBasedPresenter($property);
            if ($typeBasedPresenter !== null) {
                return $typeBasedPresenter;
            }
        }

        $valueBasedPresenter = $this->getValueBasedPresenter($value, $property);
        if ($valueBasedPresenter !== null) {
            return $valueBasedPresenter;
        }

        return $this->valueBasedPresenters['standard'];
    }

    public function getPresenterForType(string $type): ?TypePresenterInterface
    {
        $this->initializeIfNeeded();
        return $this->typeBasedPresenters[$type] ?? $this->valueBasedPresenters[$type] ?? null;
    }

    public function displayValue(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $presenter = $this->getPresenterForValue($value, $property);
        return $presenter->display($value, $property, $regionContext);
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

    private function getTypeBasedPresenter(ReflectionProperty $property): ?TypePresenterInterface
    {
        $propertyType = $property->getType();
        if ($propertyType === null) {
            return null;
        }

        if ($propertyType instanceof ReflectionNamedType) {
            return $this->resolveNamedTypePresenter($propertyType);
        }

        if ($propertyType instanceof ReflectionUnionType) {
            return $this->resolveUnionTypePresenter($propertyType);
        }

        return null;
    }

    private function resolveNamedTypePresenter(ReflectionNamedType $type): ?TypePresenterInterface
    {
        $typeName = $type->getName();

        // Enum needs translation for display
        if (enum_exists($typeName)) {
            return $this->typeBasedPresenters['enum'] ?? null;
        }

        // Registered decoration presenters
        if (isset($this->typeBasedPresenters[$typeName])) {
            return $this->typeBasedPresenters[$typeName];
        }

        return null;
    }

    private function resolveUnionTypePresenter(ReflectionUnionType $unionType): ?TypePresenterInterface
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof ReflectionNamedType) {
                $presenter = $this->resolveNamedTypePresenter($type);
                if ($presenter !== null) {
                    return $presenter;
                }
            }
        }
        return null;
    }

    private function getValueBasedPresenter(mixed $value, ?ReflectionProperty $property): ?TypePresenterInterface
    {
        foreach ($this->valueBasedPresenters as $presenter) {
            if ($presenter->supports($value, $property)) {
                return $presenter;
            }
        }
        return null;
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
            'escape' => new HtmlEscapePresenter(),
            'array' => new ArrayPresenter($this->translator),
            'collection' => new CollectionPresenter($this, $this->translator),
            'datetime' => new DateTimePresenter($this->regionContext),
            'money' => $this->moneyPresenter,
            'number' => new NumberPresenter($this->regionContext),
            'obfuscated' => new ObfuscatedPresenter($this->obfuscatorManager),
            'enum' => new EnumPresenter($this->translator),
            'boolean' => new BooleanPresenter($this->translator),
            'uuid' => new UuidPresenter(),  // ← RESTORED
            'dimensions' => new DimensionsPresenter($this->regionContext, $this->translator),  // ← RESTORED
            'weight' => new WeightPresenter($this->regionContext, $this->translator),
            'price_range_bracket' => new PriceRangeBracketPresenter(
                $this->moneyPresenter,
                $this->translator,
            ),
            'price_range' => new PriceRangePresenter(
                new PriceRangeBracketPresenter($this->moneyPresenter, $this->translator),
                $this->translator,
                $this->moneyPresenter,
            ),
            'standard' => new StandardPresenter($this->translator),
        ];
    }

    private function initializeTypeBasedPresenters(): void
    {
        $this->typeBasedPresenters = [
            'bool' => new BooleanPresenter($this->translator),
            'int' => new IntegerPresenter($this->regionContext),
            'integer' => new IntegerPresenter($this->regionContext),
            'standard' => new StandardPresenter($this->translator),
            'float' => new NumberPresenter($this->regionContext),
            'string' => new HtmlEscapePresenter(),
            'array' => new ArrayPresenter($this->translator),

            // Class types - FOR DISPLAY ONLY
            DateTime::class => new DateTimePresenter($this->regionContext),
            DateTimeImmutable::class => new DateTimePresenter($this->regionContext),
            UuidInterface::class => new UuidPresenter(),  // ← RESTORED
            Money::class => App::diGet(MoneyPresenter::class),
            Weight::class => new WeightPresenter($this->regionContext, $this->translator),
            Dimensions::class => new DimensionsPresenter($this->regionContext, $this->translator),
            PriceRangeBracket::class => new PriceRangeBracketPresenter(
                $this->moneyPresenter,
                $this->translator,
            ),
            PriceRange::class => new PriceRangePresenter(
                new PriceRangeBracketPresenter($this->moneyPresenter, $this->translator),
                $this->translator,
                $this->moneyPresenter,
            ),
            // Special types
            'enum' => new EnumPresenter($this->translator),
            'object' => new ObjectPresenter($this),
            'collection' => new CollectionPresenter($this, $this->translator),
        ];
    }
}