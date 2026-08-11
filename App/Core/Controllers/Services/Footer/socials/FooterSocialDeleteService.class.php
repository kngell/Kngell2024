<?php

declare(strict_types=1);

final class FooterSocialDeleteService extends AbstractDeleteService
{
    public function __construct(
        private readonly FooterSocialModel $model,
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
        return DeletionLabel::FOOTER_SOCIAL->value;
    }

    protected function getEventName(): string
    {
        return strtolower($this->entityClass()) . '.deleted';
    }

    /**
     * @param FooterSocial $record
     *
     * @return string
     */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getName();
    }

    /**
     * @param FooterSocial $entity
     * @param string $deleteOption
     *
     * @return QueryResult
     */
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

    /** @param FooterSocial $record */
    protected function buildEventData(Entity $record): array
    {
        return [
            'cat_id' => $record->getEntityPrimarykeyValue(),
            'public_id' => null,
            'name' => $record->getName(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
        ];
    }

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new FooterSocialEvent($dto);
    }

    private function entityClass(): string
    {
        return FooterSocial::class;
    }
}
