<?php

declare(strict_types=1);

use Brick\Money\Money;

class ShippingMethodResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;

    private MoneyManager $moneyManager;
    private UserCartItemService $cartService;
    private CartData $cartData;
    private Money $cartTotal;
    private string $currency = 'EUR';
    private Weight $cartWeight;
    private ?ShippingRate $primaryRate = null;
    private ?ShippingZoneShow $primaryZone = null;

    public function __construct(
        array $image,
        ?ShippingMethodShow $shippingMethodShow,
        private HtmlSectionPresentationService $presenter,
        MoneyManager $moneyManager,
        UserCartItemService $cartService,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $shippingMethodShow, $isDefault);
        $this->moneyManager = $moneyManager;
        $this->cartService = $cartService;

        $this->cartData = $this->cartService->getCartData();
        $this->cartTotal = $this->cartData->totalPrice ?? $this->moneyManager->zero();
        $this->currency = $this->cartTotal->getCurrency()->getCurrencyCode();
        $this->cartWeight = $this->calculateCartWeight();

        // Extract data from entity
        $entity = $this->getEntity();
        if ($entity) {
            $rates = $entity->getShippingRate();
            $zones = $entity->getShippingZone();
            $this->primaryRate = !empty($rates) ? $rates[0] : null;
            $this->primaryZone = !empty($zones) ? $zones[0] : null;
        }
    }

    public function getEntity(): ?ShippingMethodShow
    {
        return parent::getEntity();
    }

    // ─── Public Display Methods ─────────────────────────────

    public function getId(): string
    {
        return $this->presenter->showField($this->getEntity(), 'id') ?? '';
    }

    public function getCode(): string
    {
        return $this->presenter->showField($this->getEntity(), 'code') ?? '';
    }

    public function getName(): string
    {
        return $this->presenter->showField($this->getEntity(), 'name') ?? '';
    }

    public function getRate(): string
    {
        return '???';
    }

    public function getDescription(): string
    {
        return $this->presenter->showField($this->getEntity(), 'description') ?? '';
    }

    public function getCarrier(): string
    {
        return $this->presenter->showField($this->getEntity(), 'carrier') ?? '';
    }

    public function getCalculatedRate(): string
    {
        $rateMoney = $this->getCalculatedRateMoney();
        return $this->moneyManager->format($rateMoney);
    }

    public function getRateDisplay(): string
    {
        if ($this->primaryRate === null) {
            return '';
        }

        if ($this->isFree()) {
            return 'Free';
        }

        $rateType = $this->primaryRate->getRateType();
        $rateValue = $this->getRateMoney($this->primaryRate);

        if ($rateValue === null) {
            return '';
        }

        return match($rateType) {
            ShippingRateType::PERCENTAGE => $rateValue->getAmount()->toFloat() . '% of subtotal',
            default => $this->moneyManager->format($this->getCalculatedRateMoney()),
        };
    }

    public function getDeliveryEstimate(): string
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return 'Delivery date to be confirmed';
        }

        $min = $entity->getMinDeliveryDays();
        $max = $entity->getMaxDeliveryDays();

        if ($min === null && $max === null) {
            return 'Delivery date to be confirmed';
        }

        if ($min === $max) {
            return "{$min} business day" . ($min > 1 ? 's' : '');
        }

        return "{$min}-{$max} business days";
    }

    public function getExpectedDeliveryDate(): ?string
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return null;
        }

        $min = $entity->getMinDeliveryDays();
        if ($min === null) {
            return null;
        }

        $date = new DateTime();
        $date->modify("+{$min} days");
        return $date->format('M d, Y');
    }

    public function getZoneName(): string
    {
        return $this->primaryZone?->getName() ?? '';
    }

    public function getZoneCountries(): string
    {
        $countries = $this->primaryZone?->getCountry() ?? [];
        $names = array_map(fn (?Country $c) => $c?->getOfficialName() ?? '', $countries);
        return implode(', ', $names);
    }

    public function getCartWeightDisplay(): string
    {
        return $this->cartWeight->getFormatted();
    }

    public function isSelected(): bool
    {
        return $this->isDefault() && $this->isAvailable();
    }

    /**
     * Check if this method is available for the current cart.
     */
    public function isAvailable(): bool
    {
        return $this->isInRange() && $this->isWeightValid();
    }

    public function toArray(): array
    {
        $entity = $this->getEntity();
        $calculatedRate = $this->getCalculatedRateMoney();

        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'carrier' => $this->getCarrier(),
            'is_default' => $this->isDefault() ?? false,
            'is_selected' => $this->isSelected(),
            'is_available' => $this->isAvailable(),
            'rate' => [
                'calculated' => (float) $calculatedRate->getAmount()->toFloat(),
                'calculated_formatted' => $this->moneyManager->format($calculatedRate),
                'display' => $this->getRateDisplay(),
            ],
            'delivery' => [
                'estimate' => $this->getDeliveryEstimate(),
                'expected_date' => $this->getExpectedDeliveryDate(),
            ],
            'zone' => [
                'name' => $this->getZoneName(),
                'countries' => $this->getZoneCountries(),
            ],
            'cart' => [
                'weight' => $this->cartWeight->toArray(),
            ],
        ];
    }

    // ─── Private Helper Methods ──────────────────────────────

    private function isFree(): bool
    {
        if ($this->primaryRate === null) {
            return false;
        }

        $rateValue = $this->getRateMoney($this->primaryRate);
        if ($rateValue === null) {
            return false;
        }

        return $this->primaryRate->getRateType() === ShippingRateType::FREE
            || $this->moneyManager->isZero($rateValue);
    }

    private function getRateMoney(ShippingRate $rate): ?Money
    {
        $rateValue = $rate->getRateValue();
        if ($rateValue instanceof Money) {
            return $rateValue;
        }

        return $this->moneyManager->createMoney(
            (string) $rateValue,
            $rate->getCurrency() ?? $this->currency,
        );
    }

    private function getCalculatedRateMoney(): Money
    {
        if ($this->primaryRate === null) {
            return $this->moneyManager->zero($this->currency);
        }

        $rateType = $this->primaryRate->getRateType();
        $rateValue = $this->getRateMoney($this->primaryRate);

        if ($rateValue === null) {
            return $this->moneyManager->zero($this->currency);
        }

        return match($rateType) {
            ShippingRateType::FREE => $this->moneyManager->zero($this->currency),
            ShippingRateType::PERCENTAGE => $this->cartTotal->multipliedBy($rateValue->getAmount()->toFloat() / 100),
            default => $rateValue,
        };
    }

    private function isInRange(): bool
    {
        if ($this->primaryRate === null) {
            return false;
        }

        $minValue = $this->primaryRate->getMinValue();
        $maxValue = $this->primaryRate->getMaxValue();

        if ($minValue !== null && $this->cartTotal->isLessThan($minValue)) {
            return false;
        }

        if ($maxValue !== null && $this->cartTotal->isGreaterThan($maxValue)) {
            return false;
        }

        return true;
    }

    private function isWeightValid(): bool
    {
        if ($this->primaryRate === null) {
            return false;
        }

        $minWeight = $this->primaryRate->getMinWeight();
        $maxWeight = $this->primaryRate->getMaxWeight();

        if ($minWeight === null && $maxWeight === null) {
            return true;
        }

        $cartWeightKg = $this->cartWeight->getValueInUnit(WeightUnits::KILOGRAM);

        if ($minWeight !== null) {
            $minWeightKg = $minWeight->getValueInUnit(WeightUnits::KILOGRAM);
            if ($cartWeightKg < $minWeightKg) {
                return false;
            }
        }

        if ($maxWeight !== null) {
            $maxWeightKg = $maxWeight->getValueInUnit(WeightUnits::KILOGRAM);
            if ($cartWeightKg > $maxWeightKg) {
                return false;
            }
        }

        return true;
    }

    private function calculateCartWeight(): Weight
    {
        $totalWeightKg = 0.0;

        foreach ($this->cartData->items as $item) {
            if (!$item instanceof CartItem) {
                continue;
            }

            $weightString = $item->getWeight();
            $weight = $this->parseWeightFromString($weightString);

            if ($weight !== null) {
                $totalWeightKg += $weight->getValueInUnit(WeightUnits::KILOGRAM) * $item->getQuantity();
            }
        }

        return Weight::fromKilograms($totalWeightKg);
    }

    private function parseWeightFromString(string $weightString): ?Weight
    {
        $weightString = trim($weightString);

        if (preg_match('/^([\d.]+)\s*(kg|g|lb|lbs|oz|ounce|ounces)$/i', $weightString, $matches)) {
            $value = (float) $matches[1];
            $unit = strtolower($matches[2]);

            $weightUnit = match($unit) {
                'kg' => WeightUnits::KILOGRAM,
                'g' => WeightUnits::GRAM,
                'lb', 'lbs' => WeightUnits::POUND,
                'oz', 'ounce', 'ounces' => WeightUnits::OUNCE,
                default => WeightUnits::KILOGRAM,
            };

            return new Weight($value, $weightUnit);
        }

        if (is_numeric($weightString)) {
            return new Weight((float) $weightString, WeightUnits::KILOGRAM);
        }

        return null;
    }
}