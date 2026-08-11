<?php

declare(strict_types=1);

class ProductDeleteService extends AbstractDeleteService
{
    public function __construct(
        private ProductModel $model,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    protected function findRecord(array $id): ?object
    {
        $product = $this->model->getProductById($id['value'], $id['key']);
        if (!$product->isTracking()) {
            $product->track();
        }
        $this->model->addToIdentityMap($product);
        return $product;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::PRODUCT->value;
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

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new ProductEvent($dto);
    }

    /**
     * @param Product $product
     * @param string $deleteOption
     *
     * @return QueryResult
     */
    protected function performDelete(
        Entity $product,
        string $deleteOption,
    ): QueryResult {
        return $this->model->deleteEntity($product, $deleteOption);
    }

    protected function getEntityManager(): mixed
    {
        return $this->model->getEntityManager();
    }

    protected function clearModelState(): void
    {
        $this->model->clearState();
    }

    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}