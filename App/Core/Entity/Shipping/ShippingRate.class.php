<?php

declare(strict_types=1);

use Brick\Money\Money;

class ShippingRate extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private int $methodId;
    private int $zoneId;
    private Money $minValue;
    private ?Money $maxValue = null;
    private ?Weight $minWeight = null;
    private ?Weight $maxWeight = null;
    private ShippingRateType $rateType = ShippingRateType::FIXED;
    private Money $rateValue;
    private string $currency = 'EUR';
    private array $conditions = [];
    private bool $isActive = true;

    public function __construct(
        private EntityDependenciesFactoryInterface $dependencies,
        ?Money $minValue = null,
        ?Money $rateValue = null,
        string $currency = 'EUR',
        array $tableAlias = [],
        array $tableMap = [],
    ) {
        $this->minValue = $minValue ?? Money::zero($currency);
        $this->rateValue = $rateValue ?? Money::zero($currency);
        parent::__construct($dependencies, $tableAlias, $tableMap);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ShippingRate
     */
    public function setId(int $id): ShippingRate
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getMethodId(): int
    {
        return $this->methodId;
    }

    /**
     * @param int $methodId
     *
     * @return ShippingRate
     */
    public function setMethodId(int $methodId): ShippingRate
    {
        $this->methodId = $methodId;

        return $this;
    }

    /**
     * @return int
     */
    public function getZoneId(): int
    {
        return $this->zoneId;
    }

    /**
     * @param int $zoneId
     *
     * @return ShippingRate
     */
    public function setZoneId(int $zoneId): ShippingRate
    {
        $this->zoneId = $zoneId;

        return $this;
    }

    /**
     * @return Money
     */
    public function getMinValue(): Money
    {
        return $this->minValue;
    }

    /**
     * @param Money $minValue
     *
     * @return ShippingRate
     */
    public function setMinValue(Money $minValue): ShippingRate
    {
        $this->minValue = $minValue;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getMaxValue(): ?Money
    {
        return $this->maxValue;
    }

    /**
     * @param null|Money $maxValue
     *
     * @return ShippingRate
     */
    public function setMaxValue(?Money $maxValue): ShippingRate
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * @return Money
     */
    public function getRateValue(): Money
    {
        return $this->rateValue;
    }

    /**
     * @param Money $rateValue
     *
     * @return ShippingRate
     */
    public function setRateValue(Money $rateValue): ShippingRate
    {
        $this->rateValue = $rateValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return ShippingRate
     */
    public function setCurrency(string $currency): ShippingRate
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     *
     * @return ShippingRate
     */
    public function setConditions(array $conditions): ShippingRate
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return ShippingRate
     */
    public function setIsActive(bool $isActive): ShippingRate
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return ShippingRateType
     */
    public function getRateType(): ShippingRateType
    {
        return $this->rateType;
    }

    /**
     * @param ShippingRateType $rateType
     *
     * @return ShippingRate
     */
    public function setRateType(ShippingRateType $rateType): ShippingRate
    {
        $this->rateType = $rateType;

        return $this;
    }

    /**
     * @return null|Weight
     */
    public function getMinWeight(): ?Weight
    {
        return $this->minWeight;
    }

    /**
     * @param null|Weight $minWeight
     *
     * @return ShippingRate
     */
    public function setMinWeight(?Weight $minWeight): ShippingRate
    {
        $this->minWeight = $minWeight;

        return $this;
    }

    /**
     * @return null|Weight
     */
    public function getMaxWeight(): ?Weight
    {
        return $this->maxWeight;
    }

    /**
     * @param null|Weight $maxWeight
     *
     * @return ShippingRate
     */
    public function setMaxWeight(?Weight $maxWeight): ShippingRate
    {
        $this->maxWeight = $maxWeight;

        return $this;
    }
}