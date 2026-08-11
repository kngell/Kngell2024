<?php

declare(strict_types=1);

abstract class AbstractCleanupPaginatedCacheListener implements EventListenerInterface
{
    use BlockTypeTrait;

    protected ?BlockType $blockType = null;

    public function __construct(
        protected readonly PaginatedCacheFactory $cacheFactory,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $this->resolveOperation($payload);
        $this->blockType = $this->resolveBlockType($event);
        $deleteOption = $payload['deletion_option'] ?? null;

        $cache = $this->cacheFactory->create(
            $this->getPaginatedAdapter(),
            $this->getCacheFolder(),
            true,
        );

        // Archive is treated as deletion for cache purposes
        if ($operation === SqlStatement::UPDATE->value && $deleteOption === 'archive') {
            $this->onDelete($cache, $this->resolveId($event));
            return null;
        }

        match ($operation) {
            SqlStatement::INSERT->value => $this->onInsert($cache),
            SqlStatement::UPDATE->value => $this->onUpdate(
                $cache,
                $this->resolveId($event),
                $event,
            ),
            SqlStatement::DELETE->value => $this->onDelete($cache, $this->resolveId($event)),
        };

        return null;
    }

    abstract protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface;

    abstract protected function getCacheFolder(): string;

    protected function getEntityId(EventInterface $event): ?string
    {
        /** @var EventDataDTO */
        $eventObj = $event->getData();

        $id = $eventObj->getEntityId() ?? $eventObj->getRecord()?->getEntityPrimarykeyValue();
        if ($id !== null) {
            return (string) $id;
        }
        return null;
    }

    protected function onInsert(PaginatedCacheService $cache): void
    {
        $cache->invalidateAll();
    }

    protected function onUpdate(PaginatedCacheService $cache, string $id, EventInterface $event): void
    {
        if (!$event->getData()->wasSkipped()) {
            $cache->invalidateEntityWithPages($id);
        }
    }

    protected function onDelete(PaginatedCacheService $cache, string $id): void
    {
        $cache->invalidateEntityAndAllPages($id);
    }

    protected function resolveOperation(array $payload): string
    {
        $operation = $payload['operation'] ?? null;

        if (!in_array($operation, [
            SqlStatement::INSERT->value,
            SqlStatement::UPDATE->value,
            SqlStatement::DELETE->value,
        ], true)) {
            throw new EventRuntimeException(
                sprintf('Unsupported or missing operation: "%s"', (string) $operation),
            );
        }

        return $operation;
    }

    protected function resolveId(EventInterface $event): string
    {
        $id = $this->getEntityId($event);

        if ($id === null) {
            throw new EventRuntimeException('No ID found for cache identifier.');
        }

        return (string) $id;
    }
}