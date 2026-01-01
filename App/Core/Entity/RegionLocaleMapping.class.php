<?php

declare(strict_types=1);

class RegionLocaleMapping extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'mapping_id')]
    private int $mappingId;

    private string $regionCode;
    private string $localeCode;
    private bool $isDefault = true;
    private bool $isActive = true;

    /**
     * @return int
     */
    public function getMappingId(): int
    {
        return $this->mappingId;
    }

    /**
     * @param int $mappingId
     *
     * @return RegionLocaleMapping
     */
    public function setMappingId(int $mappingId): RegionLocaleMapping
    {
        $this->mappingId = $mappingId;

        return $this;
    }

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
     * @return RegionLocaleMapping
     */
    public function setRegionCode(string $regionCode): RegionLocaleMapping
    {
        $this->regionCode = $regionCode;

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
     * @return RegionLocaleMapping
     */
    public function setLocaleCode(string $localeCode): RegionLocaleMapping
    {
        $this->localeCode = $localeCode;

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
     * @return RegionLocaleMapping
     */
    public function setIsDefault(bool $isDefault): RegionLocaleMapping
    {
        $this->isDefault = $isDefault;

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
     * @return RegionLocaleMapping
     */
    public function setIsActive(bool $isActive): RegionLocaleMapping
    {
        $this->isActive = $isActive;

        return $this;
    }
}