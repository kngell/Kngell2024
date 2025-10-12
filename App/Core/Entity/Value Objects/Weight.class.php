<?php

declare(strict_types=1);


final class Weight
{
    private WeightUnits $unit;
    private float $value;

    private function __construct(float $value, WeightUnits $unit)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Weight cannot be negative.');
        }
        $this->value = $value;
        $this->unit = $unit;
    }

    public static function fromKilograms(float $kg): self
    {
        return new self($kg, WeightUnits::UNIT_KILOGRAM);
    }

    public static function fromGrams(float $g): self
    {
        return new self($g, WeightUnits::UNIT_GRAM);
    }

    public function toKilograms(): float
    {
        return match ($this->unit) {
            WeightUnits::UNIT_KILOGRAM => $this->value,
            WeightUnits::UNIT_GRAM     => $this->value / 1000.0,
            WeightUnits::UNIT_POUND    => $this->value * 0.453592,
            default => throw new LogicException("Unsupported weight unit for conversion: {$this->unit->name}"),
        };
    }

    public function getDatabaseValue(): float
    {
        return $this->toKilograms();
    }
}