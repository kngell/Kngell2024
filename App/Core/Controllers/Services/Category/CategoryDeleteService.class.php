<?php

declare(strict_types=1);

final class CategoryDeleteService extends AbstractDeleteService
{
    public function __construct(
        private readonly CategoryModel $model,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    protected function findRecord(array $id): ?Category
    {
        return $this->model->getById($id['value'])?->asClass();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::CATEGORY->value;
    }

    protected function getEventName(): string
    {
        return 'category.deleted';
    }

    /**
     * @param Category $record
     *
     * @return string
     */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getName();
    }

    protected function performDelete(Entity $entity, string $deleteOption): QueryResult
    {
        return $this->model->deleteWithOptions([
            'key' => $entity->getEntityKeyField(),
            'value' => $entity->getEntityPrimarykeyValue(),
        ], $deleteOption);
    }

    protected function getEntityManager(): mixed
    {
        return $this->model->getEntityManager();
    }

    protected function clearModelState(): void
    {
        $this->model->clearState();
    }

    /** @param Category $record */
    protected function buildEventData(Entity $record): array
    {
        return [
            'cat_id' => $record->getEntityPrimarykeyValue(),
            'public_id' => $record->getPublicId(),
            'name' => $record->getName(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
        ];
    }

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new CategoryEvent($dto);
    }
}