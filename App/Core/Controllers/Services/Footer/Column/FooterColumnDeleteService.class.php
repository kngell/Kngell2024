<?php

declare(strict_types=1);

final class FooterColumnDeleteService extends AbstractDeleteService
{
    public function __construct(
        private readonly FooterMenuColumnModel $model,
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

    protected function findRecord(array $id): ?object
    {
        return $this->model->getById($id['value'])?->asClass();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_MENU_COLUMN->value;
    }

    protected function getEventName(): string
    {
        return 'footermenucolumn.deleted';
    }

    /**
     * @param FooterMenuColumn $record
     *
     * @return string
     */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getTitle();
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

    /** @param FooterMenuColumn $record */
    protected function buildEventData(Entity $record): array
    {
        return [
            'cat_id' => $record->getEntityPrimarykeyValue(),
            'public_id' => null,
            'name' => $record->getTitle(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
        ];
    }

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new FooterMenuColumEvent($dto);
    }
}
