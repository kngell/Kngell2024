<?php

declare(strict_types=1);

class HeroDeleteService extends AbstractDeleteService
{
    public function __construct(
        private HeroModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function findRecord(string $id): ?object
    {
        return $this->model->getById($id);
    }

    protected function getLabel(): string
    {
        return 'Hero Section';
    }

    protected function getEventName(): string
    {
        return 'hero.deleted';
    }

    protected function getEventClassName(): ?string
    {
        return HeroSectionEvent::class;
    }

    protected function resolveDisplayName(object $record): string
    {
        /* @var Hero $record */
        return $record->getTitle();
    }

    protected function performDelete(
        string $id,
        string $deleteOption,
    ): mixed {
        return $this->model->deleteHeroByUuId($id, $deleteOption);
    }

    protected function getEntityManager(): mixed
    {
        return $this->model->getEntityManager();
    }

    protected function clearModelState(): void
    {
        $this->model->clearState();
    }

    protected function isRecordDeleted(object $record): bool
    {
        /* @var Hero $record */
        return $record->isDeleted();
    }

    /** @param Hero $record */
    protected function buildEventData(Entity $record): array
    {
        return [
            'hero_id' => $record->getId(),
            'public_id' => $record->getPublicId(),
            'name' => $record->getTitle(),
            'main_image' => $record->getImageUrl(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
        ];
    }
}