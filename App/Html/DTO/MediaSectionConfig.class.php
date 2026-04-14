<?php

declare(strict_types=1);

class MediaSectionConfig
{
    private string $dropzoneKey;
    private string $dropzoneName;
    private string $dropzoneStyle = 'modern';
    private bool $multiple = false;
    private string $dragText;
    private string $hintText;
    private string $icon = 'icon-upload';
    private string $iconTitle = 'icon-edit';
    private array $footer = [];
    private bool $hasAltText = true;
    private string $altTextLabel = 'Alt Text';
    private string $altTextHint = '';
    private string $sectionClass = 'form-section';
    private string $wrapperClass = '';
    private string $title;
    private bool $showRequired = false;
    private array $customFields = [];

    // Getters and setters (fluent interface)

    public function getDropzoneKey(): string
    {
        return $this->dropzoneKey;
    }

    public function setDropzoneKey(string $key): self
    {
        $this->dropzoneKey = $key;
        return $this;
    }

    public function getDropzoneName(): string
    {
        return $this->dropzoneName;
    }

    public function setDropzoneName(string $name): self
    {
        $this->dropzoneName = $name;
        return $this;
    }

    public function getDropzoneStyle(): string
    {
        return $this->dropzoneStyle;
    }

    public function setDropzoneStyle(string $style): self
    {
        $this->dropzoneStyle = $style;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function getDragText(): string
    {
        return $this->dragText;
    }

    public function setDragText(string $text): self
    {
        $this->dragText = $text;
        return $this;
    }

    public function getHintText(): string
    {
        return $this->hintText;
    }

    public function setHintText(string $text): self
    {
        $this->hintText = $text;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getFooter(): array
    {
        return $this->footer;
    }

    public function setFooter(array $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    public function hasAltText(): bool
    {
        return $this->hasAltText;
    }

    public function setHasAltText(bool $has): self
    {
        $this->hasAltText = $has;
        return $this;
    }

    public function getAltTextLabel(): string
    {
        return $this->altTextLabel;
    }

    public function setAltTextLabel(string $label): self
    {
        $this->altTextLabel = $label;
        return $this;
    }

    public function getAltTextHint(): string
    {
        return $this->altTextHint;
    }

    public function setAltTextHint(string $hint): self
    {
        $this->altTextHint = $hint;
        return $this;
    }

    public function getSectionClass(): string
    {
        return $this->sectionClass;
    }

    public function setSectionClass(string $class): self
    {
        $this->sectionClass = $class;
        return $this;
    }

    public function getWrapperClass(): string
    {
        return $this->wrapperClass;
    }

    public function setWrapperClass(string $class): self
    {
        $this->wrapperClass = $class;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function showRequired(): bool
    {
        return $this->showRequired;
    }

    public function setShowRequired(bool $show): self
    {
        $this->showRequired = $show;
        return $this;
    }

    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function setCustomFields(array $fields): self
    {
        $this->customFields = $fields;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconTitle(): string
    {
        return $this->iconTitle;
    }

    /**
     * @param string $iconTitle
     *
     * @return MediaSectionConfig
     */
    public function setIconTitle(string $iconTitle): MediaSectionConfig
    {
        $this->iconTitle = $iconTitle;

        return $this;
    }
}