<?php

declare(strict_types=1);
class Brand extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId(name: 'br_id')]
    private int $id;

    private string $name;
    private string $slug;

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
     * @return Brand
     */
    public function setId(int $id): Brand
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
     * @return Brand
     */
    public function setName(string $name): Brand
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
     * @return Brand
     */
    public function setSlug(string $slug): Brand
    {
        $this->slug = $slug;

        return $this;
    }
}