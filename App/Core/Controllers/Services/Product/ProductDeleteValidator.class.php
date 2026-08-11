<?php

declare(strict_types=1);

class ProductDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private ProductModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntiKeyField();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::PRODUCT->value;
    }

    protected function findRecord(array $id): ?object
    {
        return $this->model->getProductById($id['value'], $id['key']);
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
        array $id,
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

    private function hasActiveOrders(array $id): bool
    {
        // TODO: Implement
        return false;
    }

    private function isInUserCarts(array $id): bool
    {
        // TODO: Implement
        return false;
    }
}