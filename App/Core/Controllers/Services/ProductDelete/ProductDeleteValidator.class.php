<?php

declare(strict_types=1);

class ProductDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private ProductModel $model,
    ) {
    }

    protected function getLabel(): string
    {
        return 'Product';
    }

    protected function findRecord(string $id): ?object
    {
        return $this->model->getProductById($id);
    }

    /**
     * @param Product $record
     *
     * @return string
     */
    protected function resolveDisplayName(Entity $record): string
    {
        /* @var Product $record */
        return $record->getName();
    }

    /**
     * @param Product $record
     *
     * @return string
     */
    protected function resolveDisplayImage(Entity $record): ?string
    {
        /* @var Product $record */
        return $record->getMainImage();
    }

    /**
     * @param Product $record
     *
     * @return string
     */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        /* @var Product $record */
        $result->setMetadata('sku', $record->getSku());
        $result->setMetadata('stock_quantity', $record->getStockQuantity());
    }

    protected function checkBusinessRules(
        string $id,
        object $record,
        DeletionValidatorResult $result,
    ): void {
        /* @var Product $record */
        if ($this->hasActiveOrders($id)) {
            $result->addWarning(
                'This product has active orders. '
                . 'It will be marked as unavailable but kept for order history.',
            );
        }

        if ($record->getIsFeatured()) {
            $result->addWarning(
                'This product is currently featured on the homepage.',
            );
        }

        if ($this->isInUserCarts($id)) {
            $result->addWarning(
                'This product is in user shopping carts. '
                . 'It will be removed from carts upon deletion.',
            );
        }
    }

    private function hasActiveOrders(string $id): bool
    {
        // TODO: Implement
        return false;
    }

    private function isInUserCarts(string $id): bool
    {
        // TODO: Implement
        return false;
    }
}