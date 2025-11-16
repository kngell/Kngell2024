<?php

declare(strict_types=1);

final class Weight implements JsonSerializable
{
    private float $value;
    private WeightUnits $unit;

    public function __construct(float $value, WeightUnits $unit = WeightUnits::KILOGRAM)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Weight cannot be negative.');
        }

        $this->value = $value;
        $this->unit = $unit;
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }

    // Getters
    public function getValue(): float
    {
        return $this->value;
    }

    public function getUnit(): WeightUnits
    {
        return $this->unit;
    }

    public function getValueInUnit(WeightUnits $targetUnit): float
    {
        if ($this->unit === $targetUnit) {
            return $this->value;
        }

        return match ([$this->unit, $targetUnit]) {
            [WeightUnits::GRAM, WeightUnits::KILOGRAM] => $this->value / 1000,
            [WeightUnits::KILOGRAM, WeightUnits::GRAM] => $this->value * 1000,
            [WeightUnits::POUND, WeightUnits::KILOGRAM] => $this->value * 0.453592,
            [WeightUnits::KILOGRAM, WeightUnits::POUND] => $this->value / 0.453592,
            [WeightUnits::OUNCE, WeightUnits::KILOGRAM] => $this->value * 0.0283495,
            [WeightUnits::KILOGRAM, WeightUnits::OUNCE] => $this->value / 0.0283495,
            [WeightUnits::GRAM, WeightUnits::POUND] => $this->value * 0.00220462,
            [WeightUnits::POUND, WeightUnits::GRAM] => $this->value / 0.00220462,
            default => throw new InvalidArgumentException(
                "Conversion from {$this->unit->value} to {$targetUnit->value} not supported",
            ),
        };
    }

    // Convert to different unit (returns new instance)
    public function convertTo(WeightUnits $targetUnit): self
    {
        if ($this->unit === $targetUnit) {
            return $this;
        }

        $convertedValue = $this->getValueInUnit($targetUnit);
        return new self($convertedValue, $targetUnit);
    }

    // Database storage (always store in kilograms)
    public function getDatabaseValue(): float
    {
        return $this->getValueInUnit(WeightUnits::KILOGRAM);
    }

    // Comparison methods
    public function equals(?self $other): bool
    {
        if ($other === null) {
            return false;
        }

        // Convert both to same unit for comparison
        $thisInKg = $this->getValueInUnit(WeightUnits::KILOGRAM);
        $otherInKg = $other->getValueInUnit(WeightUnits::KILOGRAM);

        return abs($thisInKg - $otherInKg) < PHP_FLOAT_EPSILON;
    }

    public function isGreaterThan(?self $other): bool
    {
        if ($other === null) {
            return true;
        }

        $thisInKg = $this->getValueInUnit(WeightUnits::KILOGRAM);
        $otherInKg = $other->getValueInUnit(WeightUnits::KILOGRAM);

        return $thisInKg > $otherInKg;
    }

    public function isLessThan(?self $other): bool
    {
        if ($other === null) {
            return false;
        }

        $thisInKg = $this->getValueInUnit(WeightUnits::KILOGRAM);
        $otherInKg = $other->getValueInUnit(WeightUnits::KILOGRAM);

        return $thisInKg < $otherInKg;
    }

    // Arithmetic operations (returns new instances)
    public function add(self $other): self
    {
        $otherInSameUnit = $other->getValueInUnit($this->unit);
        return new self($this->value + $otherInSameUnit, $this->unit);
    }

    public function subtract(self $other): self
    {
        $otherInSameUnit = $other->getValueInUnit($this->unit);
        $result = $this->value - $otherInSameUnit;

        if ($result < 0) {
            throw new InvalidArgumentException('Resulting weight cannot be negative.');
        }

        return new self($result, $this->unit);
    }

    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Multiplication factor cannot be negative.');
        }

        return new self($this->value * $factor, $this->unit);
    }

    // Formatting
    public function getFormatted(int $decimals = 2): string
    {
        return number_format($this->value, $decimals) . ' ' . $this->unit->getSymbol();
    }

    // Serialization
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit->value,
            'unit_symbol' => $this->unit->getSymbol(),
            'formatted' => $this->getFormatted(),
            'kilograms' => $this->getValueInUnit(WeightUnits::KILOGRAM),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    // Factory methods
    public static function fromKilograms(float $kg): self
    {
        return new self($kg, WeightUnits::KILOGRAM);
    }

    public static function fromGrams(float $g): self
    {
        return new self($g, WeightUnits::GRAM);
    }

    public static function fromPounds(float $lb): self
    {
        return new self($lb, WeightUnits::POUND);
    }

    public static function fromOunces(float $oz): self
    {
        return new self($oz, WeightUnits::OUNCE);
    }

    public static function fromArray(array $data): self
    {
        $unit = WeightUnits::tryFrom($data['unit'] ?? 'kg') ?? WeightUnits::KILOGRAM;
        return new self((float) ($data['value'] ?? 0), $unit);
    }

    // Zero weight
    public static function zero(): self
    {
        return new self(0, WeightUnits::KILOGRAM);
    }
}