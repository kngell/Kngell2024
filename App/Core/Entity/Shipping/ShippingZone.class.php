<?php

declare(strict_types=1);

class ShippingZone extends Entity implements TimestampableInterface
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
    private array $settings = [];
    private bool $isActive = true;
    private int $sortOrder = 0;

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
     * @return ShippingZone
     */
    public function setId(int $id): ShippingZone
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
     * @return ShippingZone
     */
    public function setName(string $name): ShippingZone
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
     * @return ShippingZone
     */
    public function setCode(string $code): ShippingZone
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
     * @return ShippingZone
     */
    public function setDescription(?string $description): ShippingZone
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
     * @return ShippingZone
     */
    public function setSettings(array $settings): ShippingZone
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
     * @return ShippingZone
     */
    public function setIsActive(bool $isActive): ShippingZone
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
     * @return ShippingZone
     */
    public function setSortOrder(int $sortOrder): ShippingZone
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}