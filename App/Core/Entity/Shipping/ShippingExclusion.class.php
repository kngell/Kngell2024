<?php

declare(strict_types=1);

class ShippingExclusion extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private ?int $methodId = null;
    private ?int $countryId = null;
    private ?int $stateId = null;
    private ?string $postalCodePattern = null;
    private ?string $reason = null;
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
     * @return ShippingExclusion
     */
    public function setId(int $id): ShippingExclusion
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getMethodId(): ?int
    {
        return $this->methodId;
    }

    /**
     * @param null|int $methodId
     *
     * @return ShippingExclusion
     */
    public function setMethodId(?int $methodId): ShippingExclusion
    {
        $this->methodId = $methodId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    /**
     * @param null|int $countryId
     *
     * @return ShippingExclusion
     */
    public function setCountryId(?int $countryId): ShippingExclusion
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getStateId(): ?int
    {
        return $this->stateId;
    }

    /**
     * @param null|int $stateId
     *
     * @return ShippingExclusion
     */
    public function setStateId(?int $stateId): ShippingExclusion
    {
        $this->stateId = $stateId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPostalCodePattern(): ?string
    {
        return $this->postalCodePattern;
    }

    /**
     * @param null|string $postalCodePattern
     *
     * @return ShippingExclusion
     */
    public function setPostalCodePattern(?string $postalCodePattern): ShippingExclusion
    {
        $this->postalCodePattern = $postalCodePattern;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param null|string $reason
     *
     * @return ShippingExclusion
     */
    public function setReason(?string $reason): ShippingExclusion
    {
        $this->reason = $reason;

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
     * @return ShippingExclusion
     */
    public function setIsActive(bool $isActive): ShippingExclusion
    {
        $this->isActive = $isActive;

        return $this;
    }
}