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
        if (!$price->getCurrency()->is($this->getCurrency())) {
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
            'min' => $this->min ? [
                'amount' => $this->min->getAmount(),
                'currency' => $this->min->getCurrency()->getCurrencyCode(),
            ] : null,
            'max' => $this->max ? [
                'amount' => $this->max->getAmount(),
                'currency' => $this->max->getCurrency()->getCurrencyCode(),
            ] : null,
            'label' => $this->label,
            'product_count' => $this->productCount,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getUrlParams(int $index): array
    {
        return [
            'price_bracket' => $index,
            'min_price' => $this->min?->getAmount(),
            'max_price' => $this->max?->getAmount(),
        ];
    }

    private function getCurrency(): string
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

        // Validate same currency
        if ($min !== null && $max !== null && !$min->getCurrency()->is($max->getCurrency())) {
            throw new InvalidArgumentException('Min and max prices must have the same currency');
        }

        return new self($min, $max, $label, $productCount);
    }

    public static function fromArray(array $data): self
    {
        $min = isset($data['min'])
            ? Money::of($data['min']['amount'], $data['min']['currency'])
            : null;

        $max = isset($data['max'])
            ? Money::of($data['max']['amount'], $data['max']['currency'])
            : null;

        return self::create(
            min: $min,
            max: $max,
            label: $data['label'],
            productCount: $data['product_count'] ?? null,
        );
    }
}