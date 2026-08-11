<?php

declare(strict_types=1);

class ShippingZoneCountry extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private int $zoneId;
    private int $countryId;
    private bool $isActive = true;

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
     * @return ShippingZoneCountry
     */
    public function setId(int $id): ShippingZoneCountry
    {
        $this->id = $id;

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
     * @return ShippingZoneCountry
     */
    public function setZoneId(int $zoneId): ShippingZoneCountry
    {
        $this->zoneId = $zoneId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountryId(): int
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     *
     * @return ShippingZoneCountry
     */
    public function setCountryId(int $countryId): ShippingZoneCountry
    {
        $this->countryId = $countryId;

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
     * @return ShippingZoneCountry
     */
    public function setIsActive(bool $isActive): ShippingZoneCountry
    {
        $this->isActive = $isActive;

        return $this;
    }
}