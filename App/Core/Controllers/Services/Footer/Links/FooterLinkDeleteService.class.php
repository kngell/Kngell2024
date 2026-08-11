<?php

declare(strict_types=1);

final class FooterLinkDeleteService extends AbstractDeleteService
{
    public function __construct(
        private readonly FooterMenuLinkModel $model,
        private ObfuscatorManager $obfuscator,
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
     * @param FooterMenuLink $record
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

    /** @param FooterMenuLink $record */
    protected function buildEventData(Entity $record): array
    {
        return [
            'id' => $record->getEntityPrimarykeyValue(),
            'name' => $record->getTitle(),
            'created_at' => $record->getCreatedAt(),
            'updated_at' => $record->getUpdatedAt(),
            'is_active' => $record->getIsActive(),
        ];
    }

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new FooterMenuLinkEvent($dto);
    }

    protected function getEntityId(Entity $record, array $flashData): mixed
    {
        $incomingId = $flashData['column_id'] ?? null;

        if ($incomingId !== null) {
            $realId = $this->obfuscator->deobfuscate($incomingId);
            if (is_int($realId)) {
                return $realId;
            }
        }
        return $record->getEntityPrimarykeyValue();
    }
}
