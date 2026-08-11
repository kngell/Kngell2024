<?php

declare(strict_types=1);

class DropzoneConfig
{
    public function __construct(
        private string $key,
        private string $fieldName = 'image_url',
        private string $dropzoneStyle = 'modern',
        private bool $multiple = false,
        private string $dragText = 'Drag & drop hero image or click to upload',
        private string $hintText = 'Recommended: 1920x1080 • Max 2MB',
        private string $icon = 'icon-upload',
        private array $wrapperClass = [],
        private ?string $title = null,
        private array $acceptedFiles = [],
        private int $maxFileSize = 2, // Max file size in MB
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getFieldName(): string
    {
        if ($this->multiple) {
            return $this->fieldName . '[]';
        }
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getDropzoneStyle(): string
    {
        return $this->dropzoneStyle;
    }

    public function setDropzoneStyle(string $dropzoneStyle): self
    {
        $this->dropzoneStyle = $dropzoneStyle;
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

    public function setDragText(string $dragText): self
    {
        $this->dragText = $dragText;
        return $this;
    }

    public function getHintText(): string
    {
        return $this->hintText;
    }

    public function setHintText(string $hintText): self
    {
        $this->hintText = $hintText;
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

    public function getWrapperClass(): array
    {
        return $this->wrapperClass;
    }

    public function setWrapperClass(array $wrapperClass): self
    {
        $this->wrapperClass = $wrapperClass;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getAcceptedFiles(): array
    {
        return $this->acceptedFiles;
    }

    /**
     * @param array $acceptedFiles
     *
     * @return DropzoneConfig
     */
    public function setAcceptedFiles(array $acceptedFiles = ['image/jpeg,image/png,image/webp']): DropzoneConfig
    {
        $this->acceptedFiles = $acceptedFiles;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     *
     * @return DropzoneConfig
     */
    public function setMaxFileSize(int $maxFileSize = 2): DropzoneConfig
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public static function create(string $key): self
    {
        return new self($key);
    }
}