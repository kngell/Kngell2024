<?php

declare(strict_types=1);

use Brick\Money\Money;
use Ramsey\Uuid\UuidInterface;

class Product extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[EntityFieldId(name: 'pdt_id')]
    private int $id; //Unique product identifier
    private UuidInterface $publicId;

    // 🔒 Required core fields
    private string $sku; //Stock Keeping Unit (unique product code)
    private string $name; //Product title
    private string $slug; //URL-friendly name (for SEO)
    private Money $price; //Base price
    private string $currency; //Currency code (e.g., USD, EUR)
    private ProductStatus $status; //Product lifecycle
    private StockStatus $stockStatus;  //Availability
    private bool $is_active = true;

    // 🟡 Optional fields
    private ?string $description = null; //Full product description
    private ?string $shortDescription = null; //Short summary for listing pages
    private ?int $categoryId = null; //Links to category table
    private ?int $brandId = null; //Links to brands table
    private ?Money $comparePrice = null; //Discounted price (nullable)
    private ?int $stockQuantity = null; //Current inventory
    private ?Weight $weight = null; //weight for shipping
    private ?float $length = null; //length for shipping
    private ?float $width = null; //width for shipping
    private ?float $height = null; //height for shipping

    private ?string $mainImage = null; //Product thumbnail image

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Product
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return Product
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;

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
     * @return Product
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Product
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @param Money $price
     * @return Product
     */
    public function setPrice(Money $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Product
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return ProductStatus
     */
    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    /**
     * @param ProductStatus $status
     * @return Product
     */
    public function setStatus(ProductStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return StockStatus
     */
    public function getStockStatus(): StockStatus
    {
        return $this->stockStatus;
    }

    /**
     * @param StockStatus $stockStatus
     * @return Product
     */
    public function setStockStatus(StockStatus $stockStatus): self
    {
        $this->stockStatus = $stockStatus;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIs_active(): bool
    {
        return $this->is_active;
    }

    /**
     * @param bool $is_active
     * @return Product
     */
    public function setIs_active(bool $is_active): self
    {
        $this->is_active = $is_active;

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
     * @return Product
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     * @return Product
     */
    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * @param null|int $categoryId
     * @return Product
     */
    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getBrandId(): ?int
    {
        return $this->brandId;
    }

    /**
     * @param null|int $brandId
     * @return Product
     */
    public function setBrandId(?int $brandId): self
    {
        $this->brandId = $brandId;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getComparePrice(): ?Money
    {
        return $this->comparePrice;
    }

    /**
     * @param null|Money $comparePrice
     * @return Product
     */
    public function setComparePrice(?Money $comparePrice): self
    {
        $this->comparePrice = $comparePrice;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    /**
     * @param null|int $stockQuantity
     * @return Product
     */
    public function setStockQuantity(?int $stockQuantity): self
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * @return null|Weight
     */
    public function getWeight(): ?Weight
    {
        return $this->weight;
    }

    /**
     * @param null|Weight $weight
     * @return Product
     */
    public function setWeight(?Weight $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getLength(): ?float
    {
        return $this->length;
    }

    /**
     * @param null|float $length
     * @return Product
     */
    public function setLength(?float $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getWidth(): ?float
    {
        return $this->width;
    }

    /**
     * @param null|float $width
     * @return Product
     */
    public function setWidth(?float $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param null|float $height
     * @return Product
     */
    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    /**
     * @param null|string $mainImage
     * @return Product
     */
    public function setMainImage(?string $mainImage): self
    {
        $this->mainImage = $mainImage;

        return $this;
    }
}