<?php

declare(strict_types=1);

class StockStatus extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId()]
    private int $id; //Unique product identifier

    private ?StockStatusCode $stockStatusCode;
    private string $label;
    private string $description;
    private int $sortOrder;

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
     * @return StockStatus
     */
    public function setId(int $id): StockStatus
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|StockStatusCode
     */
    public function getStockStatusCode(): ?StockStatusCode
    {
        return $this->stockStatusCode;
    }

    /**
     * @param null|StockStatusCode $stockStatusCode
     *
     * @return StockStatus
     */
    public function setStockStatusCode(?StockStatusCode $stockStatusCode): StockStatus
    {
        $this->stockStatusCode = $stockStatusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return StockStatus
     */
    public function setLabel(string $label): StockStatus
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return StockStatus
     */
    public function setDescription(string $description): StockStatus
    {
        $this->description = $description;

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
     * @return StockStatus
     */
    public function setSortOrder(int $sortOrder): StockStatus
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}