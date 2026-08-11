<?php

declare(strict_types=1);

class ShippingMethod extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private string $name;
    private string $code;
    private ?string $description = null;
    private ?string $carrier = null;
    private ShippingMethodType $shippingMethodType = ShippingMethodType::FLAT_RATE;
    private bool $isActive = true;
    private bool $isDefault = false;
    private int $sortOrder = 0;
    private array $settings = [];
    private ?int $minDeliveryDays = null;
    private ?int $maxDeliveryDays = null;

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
     * @return ShippingMethod
     */
    public function setId(int $id): ShippingMethod
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ShippingMethod
     */
    public function setName(string $name): ShippingMethod
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return ShippingMethod
     */
    public function setCode(string $code): ShippingMethod
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return ShippingMethod
     */
    public function setDescription(?string $description): ShippingMethod
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    /**
     * @param null|string $carrier
     *
     * @return ShippingMethod
     */
    public function setCarrier(?string $carrier): ShippingMethod
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * @return ShippingMethodType
     */
    public function getShippingMethodType(): ShippingMethodType
    {
        return $this->shippingMethodType;
    }

    /**
     * @param ShippingMethodType $shippingMethodType
     *
     * @return ShippingMethod
     */
    public function setShippingMethodType(ShippingMethodType $shippingMethodType): ShippingMethod
    {
        $this->shippingMethodType = $shippingMethodType;

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
     * @return ShippingMethod
     */
    public function setIsActive(bool $isActive): ShippingMethod
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     *
     * @return ShippingMethod
     */
    public function setIsDefault(bool $isDefault): ShippingMethod
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return ShippingMethod
     */
    public function setSortOrder(int $sortOrder): ShippingMethod
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return ShippingMethod
     */
    public function setSettings(array $settings): ShippingMethod
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getMinDeliveryDays(): ?int
    {
        return $this->minDeliveryDays;
    }

    /**
     * @param null|int $minDeliveryDays
     *
     * @return ShippingMethod
     */
    public function setMinDeliveryDays(?int $minDeliveryDays): ShippingMethod
    {
        $this->minDeliveryDays = $minDeliveryDays;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getMaxDeliveryDays(): ?int
    {
        return $this->maxDeliveryDays;
    }

    /**
     * @param null|int $maxDeliveryDays
     *
     * @return ShippingMethod
     */
    public function setMaxDeliveryDays(?int $maxDeliveryDays): ShippingMethod
    {
        $this->maxDeliveryDays = $maxDeliveryDays;

        return $this;
    }
}