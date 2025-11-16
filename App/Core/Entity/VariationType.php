<?php

declare(strict_types=1);

class VariationType extends Entity
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[EntityFieldId()]
    private int $id;

    private string $name;
    private ?string $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return VariationType
     */
    public function setId(int $id): self
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
     * @return VariationType
     */
    public function setName(string $name): self
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
     * @return VariationType
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}