<?php

declare(strict_types=1);

class PageSection extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'cat_id', type: FieldType::INT)]
    private int $id;

    private string $sectionKey;
    private string $sectionName;
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
     * @return PageSection
     */
    public function setId(int $id): PageSection
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectionKey(): string
    {
        return $this->sectionKey;
    }

    /**
     * @param string $sectionKey
     *
     * @return PageSection
     */
    public function setSectionKey(string $sectionKey): PageSection
    {
        $this->sectionKey = $sectionKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    /**
     * @param string $sectionName
     *
     * @return PageSection
     */
    public function setSectionName(string $sectionName): PageSection
    {
        $this->sectionName = $sectionName;

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
     * @return PageSection
     */
    public function setIsActive(bool $isActive): PageSection
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
     * @return PageSection
     */
    public function setSortOrder(int $sortOrder): PageSection
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}