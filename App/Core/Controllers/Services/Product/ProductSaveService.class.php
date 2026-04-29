<?php

declare(strict_types=1);

class ProductSaveService implements SaveServiceInterface
{
    public function __construct(
        private ProductModel $productModel,
    ) {
    }

    public function getValidationRules(): string
    {
        return 'productRules';
    }

    public function getModel(): Model
    {
        return $this->productModel;
    }

    public function getEntityName(): string
    {
        return 'product';
    }

    public function getEventClass(): string
    {
        return ProductEvent::class;
    }

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array
    {
        return [
            'main_image' => $uploadService->getFilePath('main_image[]'),
            'main_video' => $uploadService->getFilePath('main_video'),
            'img_gallery' => $uploadService->getMultiFilePaths('img_gallery[]'),
        ];
    }

    public function buildEventData(
        array $formData,
        array $filePaths,
        string $operationType,
        int $entityId,
        bool $wasSkipped,
        array $modelData = [],
    ): array {
        return [
            'product_id' => $entityId,
            'operation' => $operationType,
            'was_skipped' => $wasSkipped,
            'form_data' => $formData,
            'media' => $filePaths,
            'model_data' => $modelData,
            'context' => [
                'is_new_product' => ($operationType === 'insert'),
                'has_variations' => !empty($formData['variations']),
                'has_price_change' => isset($formData['base_price']),
            ],
        ];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if ($operationType === 'INSERT' && $entityId) {
            return "/admin/{$entityId}/product-edit";
        }

        // Let navigation history handle default
        return '';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        return match($operationType) {
            'insert' => 'The product has been created successfully',
            'update' => !$wasSkipped
                ? 'The product has been updated successfully'
                : 'No changes were made to the product',
            default => 'Product operation completed',
        };
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        return isset($formData['pdt_id']) ? (int) $formData['pdt_id'] : null;
    }
}