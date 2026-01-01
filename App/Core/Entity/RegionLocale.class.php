<?php

declare(strict_types=1);

class RegionLocale extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'locale_id')]
    private int $localeId;

    private string $localeCode;
    private string $localeName;
    private string $languageCode;
    private string $countryCode;
    private bool $isActive = true;
    private bool $isDefault = false;

    /**
     * @return int
     */
    public function getLocaleId(): int
    {
        return $this->localeId;
    }

    /**
     * @param int $localeId
     *
     * @return RegionLocale
     */
    public function setLocaleId(int $localeId): RegionLocale
    {
        $this->localeId = $localeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocaleCode(): string
    {
        return $this->localeCode;
    }

    /**
     * @param string $localeCode
     *
     * @return RegionLocale
     */
    public function setLocaleCode(string $localeCode): RegionLocale
    {
        $this->localeCode = $localeCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocaleName(): string
    {
        return $this->localeName;
    }

    /**
     * @param string $localeName
     *
     * @return RegionLocale
     */
    public function setLocaleName(string $localeName): RegionLocale
    {
        $this->localeName = $localeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     *
     * @return RegionLocale
     */
    public function setLanguageCode(string $languageCode): RegionLocale
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return RegionLocale
     */
    public function setCountryCode(string $countryCode): RegionLocale
    {
        $this->countryCode = $countryCode;

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
     * @return RegionLocale
     */
    public function setIsActive(bool $isActive): RegionLocale
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
     * @return RegionLocale
     */
    public function setIsDefault(bool $isDefault): RegionLocale
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}