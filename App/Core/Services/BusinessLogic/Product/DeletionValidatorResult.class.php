<?php

declare(strict_types=1);

class DeletionValidatorResult
{
    private bool $isValid = false;
    private array $errors = [];
    private array $warnings = [];
    private ?string $productName = null;
    private ?string $productSku = null;
    private ?string $mainImage = null;
    private ?int $stockQuantity = null;
    private ?Product $productEntity = null;
    private bool $softDelete = false;

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setValid(bool $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    public function addWarning(string $warning): self
    {
        $this->warnings[] = $warning;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getErrorMessage(): string
    {
        return !empty($this->errors) ? implode(' ', $this->errors) : 'Validation failed.';
    }

    public function getValidationDetails(): array
    {
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'main_image' => $this->mainImage,
            'has_entity' => $this->productEntity !== null,
        ];
    }

    public function setProductName(?string $name): self
    {
        $this->productName = $name;
        return $this;
    }

    public function setProductSku(?string $sku): self
    {
        $this->productSku = $sku;
        return $this;
    }

    public function setProductEntity(?Product $entity): self
    {
        $this->productEntity = $entity;
        return $this;
    }

    public function getProductEntity(): ?Product
    {
        return $this->productEntity;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @return null|string
     */
    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    /**
     * @param null|string $mainImage
     *
     * @return DeletionValidatorResult
     */
    public function setMainImage(?string $mainImage): DeletionValidatorResult
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProductSku(): ?string
    {
        return $this->productSku;
    }

    /**
     * @return null|int
     */
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    /**
     * @param null|int $stockQuantity
     *
     * @return DeletionValidatorResult
     */
    public function setStockQuantity(?int $stockQuantity): DeletionValidatorResult
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSoftDelete(): bool
    {
        return $this->softDelete;
    }

    /**
     * @param bool $softDelete
     *
     * @return DeletionValidatorResult
     */
    public function setSoftDelete(bool $softDelete): DeletionValidatorResult
    {
        $this->softDelete = $softDelete;

        return $this;
    }
}