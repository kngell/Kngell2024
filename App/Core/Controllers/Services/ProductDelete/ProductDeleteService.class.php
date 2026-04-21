<?php

declare(strict_types=1);

class ProductDeleteService extends AbstractDeleteService
{
    public function __construct(
        private ProductModel $model,
        private ProductDeleteValidator $validator,
    ) {
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return 'Product';
    }

    protected function getEventName(): string
    {
        return 'product.deleted';
    }

    protected function getEventClassName(): ?string
    {
        return ProductEvent::class;
    }

    protected function resolveDisplayName(object $record): string
    {
        /* @var Product $record */
        return $record->getName();
    }

    protected function performDelete(string $id, string $deleteOption): mixed
    {
        return $this->model->deleteProductByUuId($id, $deleteOption);
    }

    protected function getEntityManager(): mixed
    {
        return $this->model->getEntityManager();
    }

    protected function clearModelState(): void
    {
        $this->model->clearState();
    }

    protected function isRecordDeleted(object $record): bool
    {
        /* @var Product $record */
        return $record->isDeleted();
    }

    /**
     * @param Product $record
     *
     * @return array
     */
    protected function buildEventData(Entity $record): array
    {
        /* @var Product $record */
        return [
            'pdt_id' => $record->getId(),
            'public_id' => $record->getPublicId(),
            'name' => $record->getName(),
            'sku' => $record->getSku(),
            'slug' => $record->getSlug(),
            'status_id' => $record->getStatusId(),
            'category_id' => $record->getCategoryId(),
            'brand_id' => $record->getBrandId(),
            'stock_quantity' => $record->getStockQuantity(),
            'main_image' => $record->getMainImage(),
            'main_video' => $record->getMainVideo(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
            'is_featured' => $record->getIsFeatured(),
            'total_sales' => $record->getTotalSales() ?? 0,
        ];
    }
}