<?php

declare(strict_types=1);

class ProductDeleteValidator
{
    public function __construct(
        private ProductModel $productModel,
    ) {
    }

    public function validate(string $productId): DeletionValidatorResult
    {
        $result = new DeletionValidatorResult();

        /** @var null|Product */
        $productEntity = $this->productModel->getProductById($productId);

        if (!$productEntity) {
            $result->addError('Product not found.');
            return $result;
        }

        $result->setProductEntity($productEntity);
        $result->setProductName($productEntity->getName());
        $result->setProductSku($productEntity->getSku());
        $result->setMainImage($productEntity->getMainImage());
        $result->setStockQuantity($productEntity->getStockQuantity());

        if ($productEntity->hasSoftDelete() && $productEntity->isDeleted()) {
            $result->addWarning('Product is already deleted. This operation will have no effect.');
            $result->setSoftDelete(true);
        }

        // Check other business rules
        $this->checkBusinessRules($productId, $productEntity, $result);

        // If no errors, mark as valid
        if (empty($result->getErrors())) {
            $result->setValid(true);
        }

        return $result;
    }

    private function checkBusinessRules(
        string $productId,
        Product $product,
        DeletionValidatorResult $result,
    ): void {
        // 1. Check for active orders
        if ($this->hasActiveOrders($productId)) {
            $result->addWarning(
                'This product has active orders. ' .
                'It will be marked as unavailable but kept for order history.',
            );
        }

        // 2. Check if product is in any active promotions
        if ($this->isInActivePromotions($productId)) {
            $result->addWarning(
                'This product is part of active promotions. ' .
                'It will be removed from promotions.',
            );
        }

        // 3. Check if product has pending reviews
        if ($this->hasPendingReviews($productId)) {
            $result->addWarning(
                'This product has pending reviews that will be archived.',
            );
        }

        // 4. Check if product is featured
        if ($product->getIsFeatured()) {
            $result->addWarning(
                'This product is currently featured on the homepage.',
            );
        }

        // 5. Check if product is in user carts
        if ($this->isInUserCarts($productId)) {
            $result->addWarning(
                'This product is in user shopping carts. ' .
                'It will be removed from carts upon deletion.',
            );
        }

        // Add more business rules as needed...
    }

    private function hasActiveOrders(string $productId): bool
    {
        // TODO: Implement with OrderModel
        // Example:
        // return $this->orderRepository->hasActiveOrdersForProduct($productId);
        return false;
    }

    private function isInActivePromotions(string $productId): bool
    {
        // TODO: Implement with PromotionModel
        return false;
    }

    private function hasPendingReviews(string $productId): bool
    {
        // TODO: Implement with ReviewModel
        return false;
    }

    private function isInUserCarts(string $productId): bool
    {
        // TODO: Implement with CartModel
        return false;
    }
}