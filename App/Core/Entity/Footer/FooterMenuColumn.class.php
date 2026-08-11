<?php

declare(strict_types=1);

class FooterMenuColumn extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private string $columnKey;
    private string $title;
    private int $sortOrder = 0;
    private bool $isActive = true;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;

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
     * @return FooterMenuColumn
     */
    public function setId(int $id): FooterMenuColumn
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnKey(): string
    {
        return $this->columnKey;
    }

    /**
     * @param string $columnKey
     *
     * @return FooterMenuColumn
     */
    public function setColumnKey(string $columnKey): FooterMenuColumn
    {
        $this->columnKey = $columnKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return FooterMenuColumn
     */
    public function setTitle(string $title): FooterMenuColumn
    {
        $this->title = $title;

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
     * @return FooterMenuColumn
     */
    public function setSortOrder(int $sortOrder): FooterMenuColumn
    {
        $this->sortOrder = $sortOrder;

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
     * @return FooterMenuColumn
     */
    public function setIsActive(bool $isActive): FooterMenuColumn
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    /**
     * @param null|DateTimeImmutable $validFrom
     *
     * @return FooterMenuColumn
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): FooterMenuColumn
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getValidTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    /**
     * @param null|DateTimeImmutable $validTo
     *
     * @return FooterMenuColumn
     */
    public function setValidTo(?DateTimeImmutable $validTo): FooterMenuColumn
    {
        $this->validTo = $validTo;

        return $this;
    }
}