<?php

declare(strict_types=1);

class Country extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private string $isoCode;
    private string $iso3Code;
    private string $numericCode;
    private string $officialName;
    private bool $postalCodeRequired = true;
    private ?string $postalCodeRegex = null;
    private bool $stateRequired = false;
    private string $stateLabel = 'State/Province';
    private ?string $phonePrefix = null;
    private ?string $region = null;
    private ?string $subregion = null;
    private float $vatRate = 0;
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
     * @return Country
     */
    public function setId(int $id): Country
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     *
     * @return Country
     */
    public function setIsoCode(string $isoCode): Country
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso3Code(): string
    {
        return $this->iso3Code;
    }

    /**
     * @param string $iso3Code
     *
     * @return Country
     */
    public function setIso3Code(string $iso3Code): Country
    {
        $this->iso3Code = $iso3Code;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumericCode(): string
    {
        return $this->numericCode;
    }

    /**
     * @param string $numericCode
     *
     * @return Country
     */
    public function setNumericCode(string $numericCode): Country
    {
        $this->numericCode = $numericCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfficialName(): string
    {
        return $this->officialName;
    }

    /**
     * @param string $officialName
     *
     * @return Country
     */
    public function setOfficialName(string $officialName): Country
    {
        $this->officialName = $officialName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPostalCodeRequired(): bool
    {
        return $this->postalCodeRequired;
    }

    /**
     * @param bool $postalCodeRequired
     *
     * @return Country
     */
    public function setPostalCodeRequired(bool $postalCodeRequired): Country
    {
        $this->postalCodeRequired = $postalCodeRequired;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPostalCodeRegex(): ?string
    {
        return $this->postalCodeRegex;
    }

    /**
     * @param null|string $postalCodeRegex
     *
     * @return Country
     */
    public function setPostalCodeRegex(?string $postalCodeRegex): Country
    {
        $this->postalCodeRegex = $postalCodeRegex;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStateRequired(): bool
    {
        return $this->stateRequired;
    }

    /**
     * @param bool $stateRequired
     *
     * @return Country
     */
    public function setStateRequired(bool $stateRequired): Country
    {
        $this->stateRequired = $stateRequired;

        return $this;
    }

    /**
     * @return string
     */
    public function getStateLabel(): string
    {
        return $this->stateLabel;
    }

    /**
     * @param string $stateLabel
     *
     * @return Country
     */
    public function setStateLabel(string $stateLabel): Country
    {
        $this->stateLabel = $stateLabel;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhonePrefix(): ?string
    {
        return $this->phonePrefix;
    }

    /**
     * @param null|string $phonePrefix
     *
     * @return Country
     */
    public function setPhonePrefix(?string $phonePrefix): Country
    {
        $this->phonePrefix = $phonePrefix;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param null|string $region
     *
     * @return Country
     */
    public function setRegion(?string $region): Country
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubregion(): ?string
    {
        return $this->subregion;
    }

    /**
     * @param null|string $subregion
     *
     * @return Country
     */
    public function setSubregion(?string $subregion): Country
    {
        $this->subregion = $subregion;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    /**
     * @param float $vatRate
     *
     * @return Country
     */
    public function setVatRate(float $vatRate): Country
    {
        $this->vatRate = $vatRate;

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
     * @return Country
     */
    public function setIsActive(bool $isActive): Country
    {
        $this->isActive = $isActive;

        return $this;
    }
}