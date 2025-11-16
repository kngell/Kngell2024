<?php

declare(strict_types=1);

class Dimensions implements JsonSerializable
{
    public function __construct(
        private ?float $length = null,
        private ?float $width = null,
        private ?float $height = null,
        private Units $unit = Units::CM,
    ) {
        $this->validate();
    }

    public function __toString(): string
    {
        return $this->getFormattedDimensions();
    }

    // Getters
    public function getLength(): ?float
    {
        return $this->length;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function getUnit(): Units
    {
        return $this->unit;
    }

    // Calculated properties
    public function getVolume(): ?float
    {
        if ($this->length === null || $this->width === null || $this->height === null) {
            return null;
        }

        return $this->length * $this->width * $this->height;
    }

    public function getFormattedVolume(): ?string
    {
        $volume = $this->getVolume();
        if ($volume === null) {
            return null;
        }

        $volumeUnit = match($this->unit) {
            'cm', 'mm' => 'cm³',
            'm' => 'm³',
            'in', 'ft' => 'cu ' . $this->unit,
            default => $this->unit . '³'
        };

        return number_format($volume, 2) . ' ' . $volumeUnit;
    }

    public function getFormattedDimensions(): string
    {
        $parts = [];
        if ($this->length !== null) {
            $parts[] = $this->length;
        }
        if ($this->width !== null) {
            $parts[] = $this->width;
        }
        if ($this->height !== null) {
            $parts[] = $this->height;
        }

        if (empty($parts)) {
            return 'Not specified';
        }

        return implode(' × ', $parts) . ' ' . $this->unit->value;
    }

    // Conversions
    public function convertTo(string $newUnit): self
    {
        if ($this->unit === $newUnit) {
            return $this;
        }

        $conversionRates = [
            Units::MM->value => ['cm' => 0.1, 'm' => 0.001, 'in' => 0.0393701, 'ft' => 0.00328084],
            Units::CM->value => ['mm' => 10, 'm' => 0.01, 'in' => 0.393701, 'ft' => 0.0328084],
            Units::M->value => ['mm' => 1000, 'cm' => 100, 'in' => 39.3701, 'ft' => 3.28084],
            Units::INCH->value => ['mm' => 25.4, 'cm' => 2.54, 'm' => 0.0254, 'ft' => 0.0833333],
            Units::FT->value => ['mm' => 304.8, 'cm' => 30.48, 'm' => 0.3048, 'in' => 12],
        ];

        $rate = $conversionRates[$this->unit][$newUnit] ?? null;
        if ($rate === null) {
            throw new InvalidArgumentException("Conversion from {$this->unit} to {$newUnit} not supported");
        }

        return new self(
            $this->length !== null ? $this->length * $rate : null,
            $this->width !== null ? $this->width * $rate : null,
            $this->height !== null ? $this->height * $rate : null,
            Units::tryFrom($newUnit),
        );
    }

    // Comparisons
    public function equals(?self $other): bool
    {
        if ($other === null) {
            return false;
        }

        // Convert both to same unit for comparison
        $thisInBase = $this->convertTo('cm');
        $otherInBase = $other->convertTo('cm');

        return $thisInBase->length === $otherInBase->length &&
               $thisInBase->width === $otherInBase->width &&
               $thisInBase->height === $otherInBase->height;
    }

    // Shipping calculations
    public function getVolumetricWeight(float $factor = 5000): ?float
    {
        $volume = $this->getVolume();
        if ($volume === null) {
            return null;
        }

        return $volume / $factor; // Standard volumetric weight calculation
    }

    public function fitsInside(self $container): bool
    {
        if ($this->length === null || $this->width === null || $this->height === null ||
            $container->length === null || $container->width === null || $container->height === null) {
            return false;
        }

        // Convert both to same unit
        $thisInContainerUnit = $this->convertTo($container->unit->value);

        return $thisInContainerUnit->length <= $container->length &&
               $thisInContainerUnit->width <= $container->width &&
               $thisInContainerUnit->height <= $container->height;
    }

    // Serialization
    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'unit' => $this->unit,
            'volume' => $this->getVolume(),
            'formatted' => $this->getFormattedDimensions(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function validate(): void
    {
        if ($this->length !== null && $this->length < 0) {
            throw new InvalidArgumentException('Length cannot be negative');
        }

        if ($this->width !== null && $this->width < 0) {
            throw new InvalidArgumentException('Width cannot be negative');
        }

        if ($this->height !== null && $this->height < 0) {
            throw new InvalidArgumentException('Height cannot be negative');
        }

        $validUnits = ['cm', 'm', 'in', 'ft', 'mm'];
        if (!in_array($this->unit, $validUnits, true)) {
            throw new InvalidArgumentException('Invalid unit. Must be one of: ' . implode(', ', $validUnits));
        }
    }

    // Static constructors
    public static function fromArray(array $data): self
    {
        return new self(
            $data['length'] ?? null,
            $data['width'] ?? null,
            $data['height'] ?? null,
            $data['unit'] ?? 'cm',
        );
    }

    public static function createCube(float $side, string $unit = 'cm'): self
    {
        return new self($side, $side, $side, Units::tryFrom($unit));
    }
}