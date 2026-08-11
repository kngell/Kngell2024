<?php

declare(strict_types=1);

class FooterMenuShow extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;
    protected const array RELATIONSHIPS = [
        'footer_menu_link' => [
            'class' => FooterMenuLink::class,
            'type' => 'one-to-many',
            'collection' => true,
            'foreign_key' => 'column_id',
        ],
    ];

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    #[NotPersisted()]
    private string $defaultTableName = 'footer_menu_column';

    private string $columnKey;
    private string $title;
    private int $sortOrder = 0;
    private bool $isActive = true;

    /** @var FooterMenuLink[] */
    private array $footerMenuLink = [];

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
     * @return FooterMenuShow
     */
    public function setId(int $id): FooterMenuShow
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
     * @return FooterMenuShow
     */
    public function setColumnKey(string $columnKey): FooterMenuShow
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
     * @return FooterMenuShow
     */
    public function setTitle(string $title): FooterMenuShow
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
     * @return FooterMenuShow
     */
    public function setSortOrder(int $sortOrder): FooterMenuShow
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
     * @return FooterMenuShow
     */
    public function setIsActive(bool $isActive): FooterMenuShow
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return FooterMenuLink[]
     */
    public function getFooterMenuLink(): array
    {
        return $this->footerMenuLink;
    }

    /**
     * @param array $footerMenuLink
     *
     * @return FooterMenuShow
     */
    public function setFooterMenuLink(array $footerMenuLink): FooterMenuShow
    {
        $this->footerMenuLink = $footerMenuLink;

        return $this;
    }
}