<?php

declare(strict_types=1);

class ShippingMethodShow extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'shipping_rate' => [
            'class' => ShippingRate::class,
            'type' => 'one-to-many',
            'collection' => true,
            'foreign_key' => 'method_id',
        ],
        'shipping_zone' => [
            'class' => ShippingZoneShow::class,
            'type' => 'many-to-many',
            'collection' => true,
        ],
        // 'country' => [
        //     'class' => Country::class,
        //     'type' => 'one-to-many',
        //     'collection' => true,
        // ],
    ];

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
    private array $shippingRate = [];
    private array $shippingZone = [];

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
     * @return ShippingMethodShow
     */
    public function setId(int $id): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setName(string $name): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setCode(string $code): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setDescription(?string $description): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setCarrier(?string $carrier): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setShippingMethodType(ShippingMethodType $shippingMethodType): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setIsActive(bool $isActive): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setIsDefault(bool $isDefault): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setSortOrder(int $sortOrder): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setSettings(array $settings): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setMinDeliveryDays(?int $minDeliveryDays): ShippingMethodShow
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
     * @return ShippingMethodShow
     */
    public function setMaxDeliveryDays(?int $maxDeliveryDays): ShippingMethodShow
    {
        $this->maxDeliveryDays = $maxDeliveryDays;

        return $this;
    }

    /**
     * @return array
     */
    public function getShippingRate(): array
    {
        return $this->shippingRate;
    }

    /**
     * @param array $shippingRate
     *
     * @return ShippingMethodShow
     */
    public function setShippingRate(array $shippingRate): ShippingMethodShow
    {
        $this->shippingRate = $shippingRate;

        return $this;
    }

    /**
     * @return array
     */
    public function getShippingZone(): array
    {
        return $this->shippingZone;
    }

    /**
     * @param array $shippingZone
     *
     * @return ShippingMethodShow
     */
    public function setShippingZone(array $shippingZone): ShippingMethodShow
    {
        $this->shippingZone = $shippingZone;

        return $this;
    }
}