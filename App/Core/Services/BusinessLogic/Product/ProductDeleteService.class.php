<?php

declare(strict_types=1);

class ProductDeleteService
{
    public function __construct(
        private ProductModel $productModel,
        private ProductDeleteValidator $validator,
        private PaginatedCacheFactory $factory,
    ) {
    }

    public function deleteProduct(string $productId, ?EventManagerInterface $eventManager = null, string $deleteOption = 'archive'): DeleteResult
    {
        $validationResult = $this->validator->validate($productId);
        if (!$validationResult->isValid()) {
            return DeleteResult::failure(
                $validationResult->getErrorMessage(),
                $validationResult->getValidationDetails(),
            );
        }

        // Check if trying to archive an already archived product
        if ($validationResult->isSoftDelete() && $deleteOption === 'archive') {
            return DeleteResult::success([
                'product_id' => $productId,
                'product_name' => $validationResult->getProductName(),
                'affected_rows' => 0,
                'was_skipped' => true,
                'skip_reason' => 'Product was already archived',
                'is_soft_deleted' => true,
                'warnings' => $validationResult->getWarnings(),
                'message' => 'Product was already archived',
                'deletion_type' => $deleteOption,
            ]);
        }

        $productEntity = $validationResult->getProductEntity();
        $productData = $this->getProductDataForEvents($productEntity);

        $this->productModel->clearState();

        try {
            $deleteResult = $this->productModel->deleteProductByUuId($productId, $deleteOption);

            if (!$deleteResult->isSuccess()) {
                $this->productModel->clearState();
                return DeleteResult::failure(
                    'Product deletion failed.',
                    ['product_id' => $productId, 'skip_reason' => $deleteResult->getSkipReason()],
                );
            }

            // Handle skip scenarios
            if ($deleteResult->wasSkipped()) {
                $skipReason = $deleteResult->getSkipReason();

                // Check if product was already deleted (for archive option only)
                if ($deleteOption === 'archive' && $productEntity->isDeleted()) {
                    return DeleteResult::success([
                        'product_id' => $productId,
                        'product_name' => $productEntity->getName(),
                        'affected_rows' => 0,
                        'was_skipped' => true,
                        'skip_reason' => 'Product was already archived',
                        'is_soft_deleted' => true,
                        'warnings' => $validationResult->getWarnings(),
                        'message' => 'Product was already archived',
                        'deletion_type' => $deleteOption,
                    ]);
                }

                // Other skip reasons
                $this->productModel->clearState();
                return DeleteResult::failure(
                    'Product deletion failed: ' . $skipReason,
                    ['product_id' => $productId, 'skip_reason' => $skipReason, 'deletion_type' => $deleteOption],
                );
            }

            $this->productModel->clearState();

            if ($deleteResult->getAffectedRows() > 0 && $eventManager !== null) {
                $this->triggerPostDeletionEvents($productId, $productData, $productEntity, $eventManager, $deleteOption);
            }

            return DeleteResult::success([
                'product_id' => $productId,
                'product_name' => $productEntity->getName(),
                'product_entity' => $productEntity,
                'affected_rows' => $deleteResult->getAffectedRows(),
                'was_skipped' => false,
                'skip_reason' => '',
                'is_soft_deleted' => ($deleteOption === 'archive'),
                'warnings' => $validationResult->getWarnings(),
                'deletion_type' => $deleteOption,
                'deletion_action' => ($deleteOption === 'permanent') ? 'permanent' : 'archive',
            ]);
            $this->productModel->clearState();
        } catch (Exception $e) {
            $this->productModel->clearState();
            return DeleteResult::failure(
                'Failed to delete product: ' . $e->getMessage(),
                [
                    'product_id' => $productId,
                    'exception' => $e->getTraceAsString(),
                    'timestamp' => time(),
                    'deletion_type' => $deleteOption,
                ],
            );
        }
    }

    private function getProductDataForEvents(Product $product): array
    {
        return [
            'pdt_id' => $product->getId(),
            'public_id' => $product->getPublicId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'slug' => $product->getSlug(),
            'status_id' => $product->getStatusId(),
            'category_id' => $product->getCategoryId(),
            'brand_id' => $product->getBrandId(),
            'stock_quantity' => $product->getStockQuantity(),
            'main_image' => $product->getMainImage(),
            'main_video' => $product->getMainVideo(),
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
            'is_active' => $product->getIsActive(),
            'is_featured' => $product->getIsFeatured(),
            'total_sales' => $product->getTotalSales() ?? 0,
            'entity_id' => $product->getId(),
        ];
    }

    private function triggerPostDeletionEvents(
        string $productId,
        array $productData,
        Product $productEntity,
        EventManagerInterface $eventManager,
        string $deleteOption = 'archive',
    ): void {
        $deletionType = ($deleteOption === 'permanent') ? 'permanent' : 'soft';

        $eventData = [
            'product_id' => $productId,
            'public_id' => $productData['public_id'] ?? null,
            'product_data' => $productData,
            'product_entity' => $productEntity,
            'deletion_type' => $deletionType,
            'deletion_option' => $deleteOption,
            'timestamp' => time(),
            'performed_by' => $this->getCurrentUserId(),
        ];

        $eventManager->notify(
            new ProductEvent('product.deleted', null, $eventData),
            null,
        );
    }

    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
    }
}