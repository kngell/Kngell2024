<?php

declare(strict_types=1);

class ProductStatus extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'pdt_id')]
    private int $id;

    private ProductStatusCode $statusCode;
    private string $name;
    private ?string $description;
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
     * @return ProductStatus
     */
    public function setId(int $id): ProductStatus
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ProductStatusCode
     */
    public function getStatusCode(): ProductStatusCode
    {
        return $this->statusCode;
    }

    /**
     * @param ProductStatusCode $code
     *
     * @return ProductStatus
     */
    public function setStatusCode(ProductStatusCode $code): ProductStatus
    {
        $this->statusCode = $code;

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
     * @return ProductStatus
     */
    public function setName(string $name): ProductStatus
    {
        $this->name = $name;

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
     * @return ProductStatus
     */
    public function setDescription(?string $description): ProductStatus
    {
        $this->description = $description;

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
     * @return ProductStatus
     */
    public function setIsActive(bool $isActive): ProductStatus
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
     * @return ProductStatus
     */
    public function setSortOrder(int $sortOrder): ProductStatus
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}