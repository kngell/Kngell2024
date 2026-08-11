<?php

declare(strict_types=1);

class ContentBlockDeleteService extends AbstractDeleteService
{
    private ?BlockType $blocktype = null;

    public function __construct(
        private ContentBlockModel $model,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    /**
     * @param null|BlockType $blocktype
     *
     * @return ContentBlockDeleteService
     */
    public function setBlocktype(?BlockType $blocktype): ContentBlockDeleteService
    {
        $this->blocktype = $blocktype;

        return $this;
    }

    protected function findRecord(array $id): ?object
    {
        return $this->model->getById($id['value']);
    }

    protected function getLabel(): string
    {
        if ($this->blocktype === null) {
            throw new InvalidArgumentException('Invalid block type: ' . $this->blocktype?->value);
        }
        return DeletionLabel::CONTENT_BLOCK->getLabel($this->blocktype);
    }

    protected function getEventName(): string
    {
        return 'contentblock.deleted';
    }

    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    protected function resolveDisplayName(object $record): string
    {
        /* @var ContentBlock $record */
        return $record->getTitle();
    }

    protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent {
        return new ContentBlockEvent($dto);
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
}
