<?php

declare(strict_types=1);

class Currency extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'currency_id')]
    private int $id;

    private string $currencyCode;
    private string $currencyName;
    private string $symbol;
    private string $currencySymbol;
    private bool $isActive = true;
    private bool $isDefault = false;
    private int $fractionDigits = 2;

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
     * @return Currency
     */
    public function setId(int $id): Currency
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return Currency
     */
    public function setCurrencyCode(string $currencyCode): Currency
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyName(): string
    {
        return $this->currencyName;
    }

    /**
     * @param string $currencyName
     *
     * @return Currency
     */
    public function setCurrencyName(string $currencyName): Currency
    {
        $this->currencyName = $currencyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     *
     * @return Currency
     */
    public function setSymbol(string $symbol): Currency
    {
        $this->symbol = $symbol;

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
     * @return Currency
     */
    public function setIsActive(bool $isActive): Currency
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    /**
     * @param string $currencySymbol
     *
     * @return Currency
     */
    public function setCurrencySymbol(string $currencySymbol): Currency
    {
        $this->currencySymbol = $currencySymbol;

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
     * @return Currency
     */
    public function setIsDefault(bool $isDefault): Currency
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return int
     */
    public function getFractionDigits(): int
    {
        return $this->fractionDigits;
    }

    /**
     * @param int $fractionDigits
     *
     * @return Currency
     */
    public function setFractionDigits(int $fractionDigits): Currency
    {
        $this->fractionDigits = $fractionDigits;

        return $this;
    }
}