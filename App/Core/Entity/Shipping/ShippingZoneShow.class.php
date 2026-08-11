<?php

declare(strict_types=1);

class ShippingZoneShow extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'country' => [
            'class' => Country::class,
            'type' => 'one-to-many',
            'collection' => true,
        ],
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
    private array $settings = [];
    private bool $isActive = true;
    private int $sortOrder = 0;
    private array $country = [];

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
     * @return ShippingZoneShow
     */
    public function setId(int $id): ShippingZoneShow
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
     * @return ShippingZoneShow
     */
    public function setName(string $name): ShippingZoneShow
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
     * @return ShippingZoneShow
     */
    public function setCode(string $code): ShippingZoneShow
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
     * @return ShippingZoneShow
     */
    public function setDescription(?string $description): ShippingZoneShow
    {
        $this->description = $description;

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
     * @return ShippingZoneShow
     */
    public function setSettings(array $settings): ShippingZoneShow
    {
        $this->settings = $settings;

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
     * @return ShippingZoneShow
     */
    public function setIsActive(bool $isActive): ShippingZoneShow
    {
        $this->isActive = $isActive;

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
     * @return ShippingZoneShow
     */
    public function setSortOrder(int $sortOrder): ShippingZoneShow
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return array
     */
    public function getCountry(): array
    {
        return $this->country;
    }

    /**
     * @param array $country
     *
     * @return ShippingZoneShow
     */
    public function setCountry(array $country): ShippingZoneShow
    {
        $this->country = $country;

        return $this;
    }
}