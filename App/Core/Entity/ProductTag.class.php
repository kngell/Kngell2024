<?php

declare(strict_types=1);

class ProductTag extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'tag_id')]
    private int $id;

    private string $name;
    private string $slug;
    private ?string $colorCode;
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
     * @return ProductTag
     */
    public function setId(int $id): ProductTag
    {
        $this->id = $id;

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
     * @return ProductTag
     */
    public function setName(string $name): ProductTag
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
     *
     * @return ProductTag
     */
    public function setSlug(string $slug): ProductTag
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    /**
     * @param null|string $colorCode
     *
     * @return ProductTag
     */
    public function setColorCode(?string $colorCode): ProductTag
    {
        $this->colorCode = $colorCode;

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
     * @return ProductTag
     */
    public function setIsActive(bool $isActive): ProductTag
    {
        $this->isActive = $isActive;

        return $this;
    }
}