<?php

declare(strict_types=1);

use Brick\Money\Currency as BrickCurrency;
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

    public function getCurrencySymbol(CurrencyCodeProviderInterface $currencyProvider): string
    {
        return $currencyProvider->getCurrencySymbol($this->currencyCode) ?? $this->currencyCode;
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
            'min_price' => $this->minPrice ? [
                'amount' => $this->minPrice->getAmount(),
                'currency' => $this->minPrice->getCurrency()->getCurrencyCode(),
            ] : null,
            'max_price' => $this->maxPrice ? [
                'amount' => $this->maxPrice->getAmount(),
                'currency' => $this->maxPrice->getCurrency()->getCurrencyCode(),
            ] : null,
            'currency_code' => $this->currencyCode,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Create price ranges from brackets.
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
                : PriceRangeBracket::fromArray($bracket);

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
     * Create default price ranges based on category min/max prices.
     */
    public static function fromMinMax(Money $minPrice, Money $maxPrice, int $numberOfBrackets = 4): self
    {
        $min = (float) $minPrice->getAmount();
        $max = (float) $maxPrice->getAmount();
        $range = $max - $min;
        $step = $range / $numberOfBrackets;

        $currency = $minPrice->getCurrency();
        $brackets = [];

        for ($i = 0; $i < $numberOfBrackets; $i++) {
            $bracketMin = Money::of($min + ($step * $i), $currency);
            $bracketMax = $i === $numberOfBrackets - 1
                ? null
                : Money::of($min + ($step * ($i + 1)), $currency);

            $brackets[] = PriceRangeBracket::create(
                min: $bracketMin,
                max: $bracketMax,
                label: self::generateLabel($bracketMin, $bracketMax, $currency->getCurrencyCode()),
            );
        }

        return self::fromBrackets($brackets);
    }

    /**
     * Create from database stored array.
     */
    public static function fromArray(array $data): self
    {
        $brackets = array_map(
            fn ($bracket) => PriceRangeBracket::fromArray($bracket),
            $data['brackets'] ?? [],
        );

        return self::fromBrackets($brackets);
    }

    /**
     * Create default ranges for a category using CurrencyCodeProvider.
     */
    public static function forCategory(
        Category $category,
        CurrencyCodeProvider $currencyProvider,
        ?string $regionCode = null,
    ): self {
        $currencyCode = $currencyProvider->getSystemDefaultCurrencyCode($regionCode);
        $currency = BrickCurrency::of($currencyCode);

        $minPrice = $category->getMinPrice() ?? Money::of(0, $currency);
        $maxPrice = $category->getMaxPrice() ?? Money::of(1000, $currency);

        return self::fromMinMax($minPrice, $maxPrice);
    }

    private static function generateLabel(?Money $min, ?Money $max, string $currencyCode): string
    {
        $symbol = self::getCurrencySymbolStatic($currencyCode);

        if ($min && $max) {
            return sprintf(
                '%s%s - %s%s',
                $symbol,
                number_format((float) $min->getAmount(), 0),
                $symbol,
                number_format((float) $max->getAmount(), 0),
            );
        }
        if ($min && !$max) {
            return sprintf('%s%s+', $symbol, number_format((float) $min->getAmount(), 0));
        }
        if (!$min && $max) {
            return sprintf('Under %s%s', $symbol, number_format((float) $max->getAmount(), 0));
        }
        return 'All prices';
    }

    private static function getCurrencySymbolStatic(string $currencyCode): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'Fr',
            'CNY' => '¥',
            'INR' => '₹',
            'BRL' => 'R$',
            'ZAR' => 'R',
        ];

        return $symbols[$currencyCode] ?? $currencyCode . ' ';
    }
}