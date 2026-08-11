<?php

declare(strict_types=1);
class MediaSectionConfig extends FormSectionConfig
{
    private DropzoneConfig $dropzoneConfig;
    private string $iconTitle = 'icon-edit';
    private bool $hasAltText = true;
    private string $altTextLabel = 'Alt Text';
    private string $altTextHint = 'Describe the image for accessibility';
    private string $altTextName = 'alt_text';
    private array $footer = [];
    private array $headerSectionClass = ['form-section__header'];
    private array $bodySectionClass = ['form-section__body'];
    private array $customFields = [];
    private string $imageField = 'image_url';
    private string $productImageField = 'main_image';

    public function __construct(
        string $key,
        string $title,
        DropzoneConfig $dropzoneConfig,
        string $icon = 'icon-upload',
        string $iconTitle = 'icon-edit',
        bool $hasAltText = true,
        string $altTextLabel = 'Alt Text',
        string $altTextHint = 'Describe the image for accessibility',
        string $altTextName = 'alt_text',
        array $footer = [],
        array $headerSectionClass = ['form-section__header'],
        array $bodySectionClass = ['form-section__body'],
        array $customFields = [],
        bool $showRequired = false,
        array $wrapperClass = [],
        ?string $customRenderer = null,
        array $rowIndicesConfig = [],
        array $fieldIndicesMapping = [],
        ?string $layoutType = null,
        array $sectionClass = ['form-section'],
        ?string $sectionKey = null,
        string $imageField = 'image_url',
        string $productImageField = 'main_image',
        array $fieldMapping = [],
    ) {
        parent::__construct(
            key: $key,
            title: $title,
            icon: $icon,
            showRequired: $showRequired,
            wrapperClass: $wrapperClass,
            customRenderer: $customRenderer,
            rowIndicesConfig: $rowIndicesConfig,
            fieldIndicesMapping: $fieldIndicesMapping,
            layoutType: $layoutType,
            sectionClass: $sectionClass,
            sectionKey: $sectionKey,
            fieldMapping: $fieldMapping,
        );

        $this->dropzoneConfig = $dropzoneConfig;
        $this->iconTitle = $iconTitle;
        $this->hasAltText = $hasAltText;
        $this->altTextLabel = $altTextLabel;
        $this->altTextHint = $altTextHint;
        $this->altTextName = $altTextName;
        $this->footer = $footer;
        $this->headerSectionClass = $headerSectionClass;
        $this->bodySectionClass = $bodySectionClass;
        $this->customFields = $customFields;
        $this->imageField = $imageField;
        $this->productImageField = $productImageField;
    }

    // Getters
    public function getDropzoneConfig(): DropzoneConfig
    {
        return $this->dropzoneConfig;
    }

    public function getIconTitle(): string
    {
        return $this->iconTitle;
    }

    public function hasAltText(): bool
    {
        return $this->hasAltText;
    }

    public function getAltTextLabel(): string
    {
        return $this->altTextLabel;
    }

    public function getAltTextHint(): string
    {
        return $this->altTextHint;
    }

    public function getAltTextName(): string
    {
        return $this->altTextName;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

    public function getHeaderSectionClass(): array
    {
        return $this->headerSectionClass;
    }

    public function getBodySectionClass(): array
    {
        return $this->bodySectionClass;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function getImageField(): string
    {
        return $this->imageField;
    }

    public function getProductImageField(): string
    {
        return $this->productImageField;
    }

    // Setters
    public function setIconTitle(string $iconTitle): self
    {
        $this->iconTitle = $iconTitle;
        return $this;
    }

    public function setHasAltText(bool $hasAltText): self
    {
        $this->hasAltText = $hasAltText;
        return $this;
    }

    public function setAltTextLabel(string $altTextLabel): self
    {
        $this->altTextLabel = $altTextLabel;
        return $this;
    }

    public function setAltTextHint(string $altTextHint): self
    {
        $this->altTextHint = $altTextHint;
        return $this;
    }

    public function setAltTextName(string $altTextName): self
    {
        $this->altTextName = $altTextName;
        return $this;
    }

    public function setFooter(array $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    public function setHeaderSectionClass(array $headerSectionClass): self
    {
        $this->headerSectionClass = $headerSectionClass;
        return $this;
    }

    public function setBodySectionClass(array $bodySectionClass): self
    {
        $this->bodySectionClass = $bodySectionClass;
        return $this;
    }

    public function setCustomFields(array $customFields): self
    {
        $this->customFields = $customFields;
        return $this;
    }

    public function setImageField(string $imageField): self
    {
        $this->imageField = $imageField;
        return $this;
    }

    public function setProductImageField(string $productImageField): self
    {
        $this->productImageField = $productImageField;
        return $this;
    }

    public function getFieldsConfig(): array
    {
        $fields = [
            [
                'key' => $this->getKey(),
                'name' => $this->dropzoneConfig->getFieldName(),
                'type' => 'dropzone',
                'dropzoneStyle' => $this->dropzoneConfig->getDropzoneStyle(),
                'multiple' => $this->dropzoneConfig->isMultiple(),
                'dragText' => $this->dropzoneConfig->getDragText(),
                'hintText' => $this->dropzoneConfig->getHintText(),
                'icon' => $this->dropzoneConfig->getIcon(),
                'footer' => $this->footer,
            ],
        ];

        if ($this->hasAltText) {
            $fields[] = [
                'key' => $this->altTextName,
                'name' => $this->altTextName,
                'type' => 'text',
                'label' => $this->altTextLabel,
                'placeholder' => ' ',
                'hint' => $this->altTextHint,
            ];
        }

        if (!empty($this->customFields)) {
            $fields = array_merge($fields, $this->customFields);
        }

        return $fields;
    }

    public static function create(string $key, string $title, DropzoneConfig $dropzoneConfig): self
    {
        return new self($key, $title, $dropzoneConfig);
    }
}