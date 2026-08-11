<?php

declare(strict_types=1);
abstract class FormSectionConfig
{
    public function __construct(
        private string $key,
        private string $title,
        private string $icon = 'icon-edit',
        private bool $showRequired = false,
        private array $wrapperClass = [],
        private ?string $customRenderer = null,
        private array $rowIndicesConfig = [],
        private array $fieldIndicesMapping = [],
        private ?string $layoutType = null,
        private array $sectionClass = ['form-section'],
        private array $sectionClassHeader = ['form-section__header'],
        private array $sectionClassBody = ['form-section__body'],
        private array $sectionClassFooter = ['form-section__footer'],
        private ?string $sectionKey = null,
        private array $customAttributes = [],
        private ?string $sectionId = null,
        private ?string $sectionBodyId = null,
        private array $fieldMapping = [],
        private ?HtmlSectionInterface $sectionParent = null,
    ) {
    }

    // Getters
    public function getKey(): string
    {
        return $this->key;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function isShowRequired(): bool
    {
        return $this->showRequired;
    }

    public function getCustomRenderer(): ?string
    {
        return $this->customRenderer;
    }

    public function getRowIndicesConfig(): array
    {
        return $this->rowIndicesConfig;
    }

    public function getFieldIndicesMapping(): array
    {
        return $this->fieldIndicesMapping;
    }

    public function getLayoutType(): ?string
    {
        return $this->layoutType;
    }

    public function getSectionClass(): array
    {
        return $this->sectionClass;
    }

    public function getSectionKey(): ?string
    {
        return $this->sectionKey;
    }

    // Setters
    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function setShowRequired(bool $showRequired): static
    {
        $this->showRequired = $showRequired;
        return $this;
    }

    public function setCustomRenderer(?string $customRenderer): static
    {
        $this->customRenderer = $customRenderer;
        return $this;
    }

    public function setRowIndicesConfig(array $rowIndicesConfig): static
    {
        $this->rowIndicesConfig = $rowIndicesConfig;
        return $this;
    }

    public function setFieldIndicesMapping(array $fieldIndicesMapping): static
    {
        $this->fieldIndicesMapping = $fieldIndicesMapping;
        return $this;
    }

    public function setLayoutType(?string $layoutType): static
    {
        $this->layoutType = $layoutType;
        return $this;
    }

    public function setSectionClass(array $sectionClass): static
    {
        $this->sectionClass = array_merge($this->sectionClass, $sectionClass);
        return $this;
    }

    public function setSectionKey(?string $sectionKey): static
    {
        $this->sectionKey = $sectionKey;
        return $this;
    }

    abstract public function getFieldsConfig(): array;

    /**
     * @return array
     */
    public function getWrapperClass(): array
    {
        return $this->wrapperClass;
    }

    /**
     * @param array $wrapperClass
     *
     * @return static
     */
    public function setWrapperClass(array $wrapperClass): static
    {
        $this->wrapperClass = $wrapperClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    /**
     * @param array $customAttributes
     *
     * @return static
     */
    public function setCustomAttributes(array $customAttributes): static
    {
        $this->customAttributes = $customAttributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionClassHeader(): array
    {
        return $this->sectionClassHeader;
    }

    /**
     * @param array $sectionClassHeader
     *
     * @return static
     */
    public function setSectionClassHeader(array $sectionClassHeader): static
    {
        $this->sectionClassHeader = array_merge($this->sectionClassHeader, $sectionClassHeader);

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionClassBody(): array
    {
        return $this->sectionClassBody;
    }

    /**
     * @param array $sectionClassBody
     *
     * @return static
     */
    public function setSectionClassBody(array $sectionClassBody): static
    {
        $this->sectionClassBody = array_merge($this->sectionClassBody, $sectionClassBody);

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionClassFooter(): array
    {
        return $this->sectionClassFooter;
    }

    /**
     * @param array $sectionClassFooter
     *
     * @return static
     */
    public function setSectionClassFooter(array $sectionClassFooter): static
    {
        $this->sectionClassFooter = $sectionClassFooter;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSectionId(): ?string
    {
        return $this->sectionId;
    }

    /**
     * @param null|string $sectionId
     *
     * @return static
     */
    public function setSectionId(?string $sectionId): static
    {
        $this->sectionId = $sectionId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSectionBodyId(): ?string
    {
        return $this->sectionBodyId;
    }

    /**
     * @param null|string $sectionBodyId
     *
     * @return static
     */
    public function setSectionBodyId(?string $sectionBodyId): static
    {
        $this->sectionBodyId = $sectionBodyId;

        return $this;
    }

    /**
     * @return array
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    /**
     * @param array $fieldMapping
     *
     * @return static
     */
    public function setFieldMapping(array $fieldMapping): static
    {
        $this->fieldMapping = $fieldMapping;

        return $this;
    }

    /**
     * @return null|HtmlSectionInterface
     */
    public function getSectionParent(): ?HtmlSectionInterface
    {
        return $this->sectionParent;
    }

    /**
     * @param null|HtmlSectionInterface $sectionParent
     *
     * @return static
     */
    public function setSectionParent(?HtmlSectionInterface $sectionParent): static
    {
        $this->sectionParent = $sectionParent;

        return $this;
    }
}