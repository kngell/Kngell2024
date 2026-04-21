<?php

declare(strict_types=1);

use Brick\Money\Money;

class PriceRangeFormPresenter implements TypePresenterInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof PriceRange;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): array
    {
        if (!$value instanceof PriceRange) {
            return ['brackets' => []];
        }

        $brackets = $value->getBrackets();
        $formattedBrackets = [];

        foreach ($brackets as $bracket) {
            $formattedBrackets[] = [
                'label' => $bracket->getLabel(),
                'min' => $this->getMoneyAmount($bracket->getMin()),
                'max' => $this->getMoneyAmount($bracket->getMax()),
            ];
        }

        return [
            'brackets' => $formattedBrackets,
        ];
    }

    private function getMoneyAmount(?Money $money): ?string
    {
        if ($money === null) {
            return null;
        }
        return (string) $money->getAmount();
    }
}