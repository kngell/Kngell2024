<?php

declare(strict_types=1);

class Region extends Entity
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'region_code')]
    private string $regionCode;

    private string $regionName;
    private int $currencyId;
    private bool $isActive = true;
    private string $timezone;
    private string $locale;

    /**
     * @return string
     */
    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    /**
     * @param string $regionCode
     *
     * @return Region
     */
    public function setRegionCode(string $regionCode): Region
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegionName(): string
    {
        return $this->regionName;
    }

    /**
     * @param string $regionName
     *
     * @return Region
     */
    public function setRegionName(string $regionName): Region
    {
        $this->regionName = $regionName;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    /**
     * @param int $currencyId
     *
     * @return Region
     */
    public function setCurrencyId(int $currencyId): Region
    {
        $this->currencyId = $currencyId;

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
     * @return Region
     */
    public function setIsActive(bool $isActive): Region
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     *
     * @return Region
     */
    public function setTimezone(string $timezone): Region
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Region
     */
    public function setLocale(string $locale): Region
    {
        $this->locale = $locale;

        return $this;
    }
}