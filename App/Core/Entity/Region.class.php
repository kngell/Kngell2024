<?php

declare(strict_types=1);

class Region extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'region_code')]
    private string $regionCode;

    private string $regionName;
    private int $currencyId;
    private bool $isActive = true;
    private string $timezone;
    private string $locale;
    private ?string $defaultLocale;
    private string $decimalSeparator = '.';
    private string $thousandsSeparator = ',';
    private string $dateFormat = 'Y-m-d';
    private string $datetimeFormat = 'Y-m-d H:i:s';
    private string $timeFormat = 'H:i:s';
    private int $firstDayOfWeek = 1;

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

    /**
     * @return null|string
     */
    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    /**
     * @param null|string $defaultLocale
     *
     * @return Region
     */
    public function setDefaultLocale(?string $defaultLocale): Region
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    /**
     * @param string $decimalSeparator
     *
     * @return Region
     */
    public function setDecimalSeparator(string $decimalSeparator): Region
    {
        $this->decimalSeparator = $decimalSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getThousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }

    /**
     * @param string $thousandsSeparator
     *
     * @return Region
     */
    public function setThousandsSeparator(string $thousandsSeparator): Region
    {
        $this->thousandsSeparator = $thousandsSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     *
     * @return Region
     */
    public function setDateFormat(string $dateFormat): Region
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatetimeFormat(): string
    {
        return $this->datetimeFormat;
    }

    /**
     * @param string $datetimeFormat
     *
     * @return Region
     */
    public function setDatetimeFormat(string $datetimeFormat): Region
    {
        $this->datetimeFormat = $datetimeFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * @param string $timeFormat
     *
     * @return Region
     */
    public function setTimeFormat(string $timeFormat): Region
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    /**
     * @return int
     */
    public function getFirstDayOfWeek(): int
    {
        return $this->firstDayOfWeek;
    }

    /**
     * @param int $firstDayOfWeek
     *
     * @return Region
     */
    public function setFirstDayOfWeek(int $firstDayOfWeek): Region
    {
        $this->firstDayOfWeek = $firstDayOfWeek;

        return $this;
    }
}