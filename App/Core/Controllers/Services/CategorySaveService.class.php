<?php

declare(strict_types=1);

/**
 * WRITE-ONLY Service - For admin save operations
 * Implements SaveServiceInterface for the generic save handler.
 */
class CategorySaveService implements SaveServiceInterface
{
    public function __construct(
        private CategoryModel $categoryModel,
    ) {
    }

    public function getValidationRules(): string
    {
        return 'categoryRules'; // Your validation rules key
    }

    public function getModel(): Model
    {
        return $this->categoryModel;
    }

    public function getEntityName(): string
    {
        return Category::class;
    }

    public function getEventClass(): string
    {
        return CategoryEvent::class;
    }

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array
    {
        return [
            'image_url' => $uploadService->getFilePath('image_url'),
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
            'category_id' => $entityId,
            'operation' => $operationType,
            'was_skipped' => $wasSkipped,
            'form_data' => $formData,
            'media' => $filePaths,
            'model_data' => $modelData,
            'context' => [
                'is_new_category' => ($operationType === 'insert'),
                'parent_id' => $formData['parent_id'] ?? null,
                'level' => $formData['level'] ?? 0,
            ],
        ];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if (($operationType === 'insert' || $operationType === 'update') && $entityId) {
            return "/admin/categories/{$entityId}/edit";
        }

        return '/admin/categories';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        return match($operationType) {
            'insert' => 'Category has been created successfully',
            'update' => !$wasSkipped
                ? 'Category has been updated successfully'
                : 'No changes were made to the category',
            'delete' => 'Category has been deleted successfully',
            default => 'Category operation completed',
        };
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        return isset($formData['category_id']) ? (int) $formData['category_id'] : null;
    }

    /**
     * Additional write operations specific to categories.
     */
    public function updateOrderIndex(array $categoryOrder): bool
    {
        foreach ($categoryOrder as $index => $categoryId) {
            $result = $this->categoryModel->update($categoryId, ['order_index' => $index]);

            if (!$result->isSuccess()) {
                return false;
            }
        }

        return true;
    }

    public function toggleActive(int $categoryId, bool $isActive): bool
    {
        $result = $this->categoryModel->update($categoryId, ['is_active' => $isActive]);
        return $result->isSuccess();
    }
}