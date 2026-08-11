<?php

declare(strict_types=1);

use Brick\Money\Money;
use Psr\Log\LoggerInterface;

/**
 * @extends AbstractCollectionEntityService<ShippingMethodShow>
 */
class ShippingMethodService extends AbstractCollectionEntityService
{
    private array $conditions = [];

    public function __construct(
        private ShippingMethodShowModel $model,
        ShippingMethodShowCacheManagerFactory $factory,
        private readonly HtmlSectionPresentationService $presenter,
        private readonly MoneyManager $moneyManager,
        private readonly UserCartItemService $cartService,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($factory->create(), $logger);
    }

    #[Override]
    public function getDefaultResponse(): array
    {
        return [];
    }

    public function getShippingMethodsForCurrentCart(
        string $isoCode,
        int $limit = 50,
        int $offset = 0,
    ): array {
        $cartData = $this->cartService->getCartData();
        $cartTotal = $cartData->totalPrice ?? $this->moneyManager->zero();

        $cartWeightKg = $this->calculateCartWeightInKg($cartData);

        $this->conditions = $this->buildConditions(
            $isoCode,
            $cartTotal,
            $cartWeightKg,
            $limit,
            $offset,
        );

        return $this->getForPage('checkout');
    }

    #[Override]
    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $conditions = array_merge($this->conditions, [
            ConditionListMode::MODE_FRONTEND->value => true,
        ]);
        $dbResult = $this->model->all($conditions, true);
        if ($dbResult->isSuccess()) {
            $result = $dbResult->asClass();
            // dd($result);
            return empty($result) ? [] : $result;
        }

        return [];
    }

    protected function buildConditions(
        string $isoCode,
        Money $cartTotal,
        float $cartWeightKg,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $cartTotalAmount = (float) $cartTotal->getAmount()->toFloat();

        $conditions = [
            'shipping_rate.is_active' => true,
            'shipping_zone.is_active' => true,
            'country.is_active' => true,
            'country.iso_code' => $isoCode,
            // Cart total range filters
            '(',
            'shipping_rate.min_value IS NULL',
            'OR',
            'shipping_rate.min_value',
            '<=',
            $cartTotalAmount,
            ')',
            'AND',
            '(',
            'shipping_rate.max_value IS NULL',
            'OR',
            'shipping_rate.max_value',
            '>=',
            $cartTotalAmount,
            ')',
            // Cart weight range filters (stored in KG in database)
            'AND',
            '(',
            'shipping_rate.min_weight IS NULL',
            'OR',
            'shipping_rate.min_weight',
            '<=',
            $cartWeightKg,
            ')',
            'AND',
            '(',
            'shipping_rate.max_weight IS NULL',
            'OR',
            'shipping_rate.max_weight',
            '>=',
            $cartWeightKg,
            ')',
        ];

        if ($limit !== null) {
            $conditions['limit'] = $limit;
        }
        if ($offset !== null) {
            $conditions['offset'] = $offset;
        }

        // Add frontend mode flag
        $conditions[ConditionListMode::MODE_FRONTEND->value] = true;

        return $conditions;
    }

    #[Override]
    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $dbResult = $this->model->all(['id' => $ids, ConditionListMode::MODE_FRONTEND->value => true]);
        return $dbResult->isSuccess() ? $dbResult->asClass() : [];
    }

    #[Override]
    protected function buildResponses(array $entities): array
    {
        $responses = [];
        foreach ($entities as $entity) {
            $responses[] = $this->createResponse(
                image: [],
                entity: $entity,
                isDefault: (bool) $entity->getIsDefault(),
            );
        }
        return $responses;
    }

    /**
     * {@inheritdoc}
     */
    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): ShippingMethodResponse
    {
        return new ShippingMethodResponse(
            image: $image,
            shippingMethodShow: $entity,
            presenter: $this->presenter,
            moneyManager: $this->moneyManager,
            cartService: $this->cartService,
            isDefault: $isDefault,
        );
    }

    /**
     * Calculate total cart weight in kilograms.
     */
    private function calculateCartWeightInKg(CartData $cartData): float
    {
        $totalWeightKg = 0.0;

        foreach ($cartData->items as $item) {
            if (!$item instanceof CartItem) {
                continue;
            }

            // Get weight from cart item (stored as string with unit)
            $weightString = $item->getWeight();
            $weight = $this->parseWeightFromString($weightString);

            if ($weight !== null) {
                // Convert to kilograms for database comparison
                $totalWeightKg += $weight->getValueInUnit(WeightUnits::KILOGRAM) * $item->getQuantity();
            }
        }

        return $totalWeightKg;
    }

    private function parseWeightFromString(string $weightString): ?Weight
    {
        // Remove extra spaces
        $weightString = trim($weightString);

        // Try to match pattern: number + unit
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

        // If no unit specified, assume kilograms
        if (is_numeric($weightString)) {
            return new Weight((float) $weightString, WeightUnits::KILOGRAM);
        }

        return null;
    }
}