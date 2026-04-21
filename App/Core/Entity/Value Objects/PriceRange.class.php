<?php

declare(strict_types=1);

use Brick\Money\Money;

class PriceRange implements JsonSerializable
{
    private string $currencyCode;

    /**
     * @param array<PriceRangeBracket> $brackets
     */
    private function __construct(
        private readonly array $brackets,
        private readonly ?Money $minPrice = null,
        private readonly ?Money $maxPrice = null,
    ) {
        // Get currency from first non-null bracket
        foreach ($brackets as $bracket) {
            $min = $bracket->getMin();
            $max = $bracket->getMax();
            if ($min) {
                $this->currencyCode = $min->getCurrency()->getCurrencyCode();
                break;
            }
            if ($max) {
                $this->currencyCode = $max->getCurrency()->getCurrencyCode();
                break;
            }
        }
        $this->currencyCode ??= 'USD';
    }

    public function getBrackets(): array
    {
        return $this->brackets;
    }

    public function getBracket(int $index): ?PriceRangeBracket
    {
        return $this->brackets[$index] ?? null;
    }

    public function getMinPrice(): ?Money
    {
        return $this->minPrice;
    }

    public function getMaxPrice(): ?Money
    {
        return $this->maxPrice;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function findBracketForPrice(Money $price): ?PriceRangeBracket
    {
        foreach ($this->brackets as $bracket) {
            if ($bracket->contains($price)) {
                return $bracket;
            }
        }
        return null;
    }

    public function getActiveBracket(?int $bracketIndex): ?PriceRangeBracket
    {
        return $this->brackets[$bracketIndex] ?? null;
    }

    public function withProductCounts(array $counts): self
    {
        $updatedBrackets = [];
        foreach ($this->brackets as $index => $bracket) {
            $updatedBrackets[] = $bracket->withProductCount($counts[$index] ?? 0);
        }
        return self::fromBrackets($updatedBrackets);
    }

    public function toArray(): array
    {
        return [
            'brackets' => array_map(fn ($b) => $b->toArray(), $this->brackets),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Create from brackets array.
     */
    public static function fromBrackets(array $brackets): self
    {
        if (empty($brackets)) {
            throw new InvalidArgumentException('At least one price bracket is required');
        }

        $validatedBrackets = [];
        $previousMax = null;

        foreach ($brackets as $bracket) {
            $rangeBracket = $bracket instanceof PriceRangeBracket
                ? $bracket
                : $bracket;

            if ($previousMax !== null && $rangeBracket->getMin() !== null) {
                if ($rangeBracket->getMin()->isGreaterThan($previousMax)) {
                    throw new InvalidArgumentException('Price bracket gap detected');
                }
            }

            $validatedBrackets[] = $rangeBracket;
            $previousMax = $rangeBracket->getMax();
        }

        $firstBracket = $validatedBrackets[0];
        $lastBracket = $validatedBrackets[count($validatedBrackets) - 1];

        return new self(
            $validatedBrackets,
            $firstBracket->getMin(),
            $lastBracket->getMax(),
        );
    }

    /**
     * Create from database array (simple numeric values, no currency structure).
     */
    public static function fromDatabaseArray(array $data, string $currencyCode): self
    {
        $brackets = array_map(
            fn ($bracket) => PriceRangeBracket::fromDatabaseArray($bracket, $currencyCode),
            $data['brackets'] ?? [],
        );
        return self::fromBrackets($brackets);
    }
}