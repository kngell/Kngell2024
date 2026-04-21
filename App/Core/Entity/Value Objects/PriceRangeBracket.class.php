<?php

declare(strict_types=1);

use Brick\Money\Money;

class PriceRangeBracket implements JsonSerializable
{
    private function __construct(
        private readonly ?Money $min,
        private readonly ?Money $max,
        private readonly string $label,
        private readonly ?int $productCount = null,
    ) {
    }

    public function getMin(): ?Money
    {
        return $this->min;
    }

    public function getMax(): ?Money
    {
        return $this->max;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getProductCount(): ?int
    {
        return $this->productCount;
    }

    public function withProductCount(int $count): self
    {
        return new self($this->min, $this->max, $this->label, $count);
    }

    public function contains(Money $price): bool
    {
        if ($this->getCurrencyCode() !== $price->getCurrency()->getCurrencyCode()) {
            return false;
        }

        if ($this->min !== null && $price->isLessThan($this->min)) {
            return false;
        }

        if ($this->max !== null && $price->isGreaterThan($this->max)) {
            return false;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            // Convert BigDecimal to string for JSON serialization
            'min' => $this->min ? (string) $this->min->getAmount() : null,
            'max' => $this->max ? (string) $this->max->getAmount() : null,
            'label' => $this->label,
            'product_count' => $this->productCount,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function getCurrencyCode(): string
    {
        return ($this->min ?? $this->max)?->getCurrency()->getCurrencyCode() ?? 'USD';
    }

    public static function create(?Money $min, ?Money $max, string $label, ?int $productCount = null): self
    {
        if ($min === null && $max === null) {
            throw new InvalidArgumentException('Either min or max price must be set');
        }

        if ($min !== null && $max !== null && $min->isGreaterThan($max)) {
            throw new InvalidArgumentException('Min price cannot be greater than max price');
        }

        if ($min !== null && $max !== null &&
            $min->getCurrency()->getCurrencyCode() !== $max->getCurrency()->getCurrencyCode()) {
            throw new InvalidArgumentException('Min and max prices must have the same currency');
        }

        return new self($min, $max, $label, $productCount);
    }

    /**
     * Create from database array (simple numeric values as strings).
     */
    public static function fromDatabaseArray(array $data, string $currencyCode): self
    {
        $min = isset($data['min']) && $data['min'] !== null && $data['min'] !== ''
            ? Money::of((string) $data['min'], $currencyCode)
            : null;

        $max = isset($data['max']) && $data['max'] !== null && $data['max'] !== ''
            ? Money::of((string) $data['max'], $currencyCode)
            : null;

        return self::create(
            min: $min,
            max: $max,
            label: $data['label'],
            productCount: $data['product_count'] ?? null,
        );
    }
}