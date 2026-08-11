<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractEntityRestoreListener implements EventListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    final public function handle(EventInterface $event): RestoreResultInterface
    {
        $entity = $this->extractEntity($event);
        $entityId = $this->extractEntityId($entity);
        $archivedAt = $this->extractArchivedAt($entity);
        $payload = $this->extractPayload($event);

        try {
            return $this->performRestore($entity, $entityId, $archivedAt, $payload);
        } catch (RuntimeException $e) {
            $this->logFailure($e, $entityId, $archivedAt);
            throw $e;
        } catch (Throwable $e) {
            $this->logFailure($e, $entityId, $archivedAt);
            throw new RuntimeException(sprintf(
                '%s failed for %s %s: %s',
                static::class,
                $this->entityType(),
                (string) $entityId,
                $e->getMessage(),
            ), 0, $e);
        }
    }

    /**
     * FQCN of the entity class this listener expects on the event.
     *
     * @return class-string
     */
    abstract protected function expectedEntityClass(): string;

    /** Short, stable identifier for the entity type, e.g. 'product'. */
    abstract protected function entityType(): string;

    /**
     * Concrete listeners implement only this.
     *
     * Contract:
     *  - $entity is the still-archived parent (deleted_at NOT cleared yet).
     *  - $archivedAt is its current deleted_at, used as the cohort filter.
     *  - $payload is the raw event params (rarely needed for restore).
     *
     * @param array<string, mixed> $payload
     */
    abstract protected function performRestore(
        SoftDeletableInterface $entity,
        int|string $entityId,
        DateTimeInterface $archivedAt,
        array $payload,
    ): RestoreResultInterface;

    protected function assertSuccess(
        QueryResult $result,
        string $context,
        int|string $entityId,
    ): QueryResult {
        if (!$result->isSuccess()) {
            $reason = method_exists($result, 'getErrorMessage')
                ? $result->getErrorMessage()
                : 'unknown error';

            throw new RuntimeException(sprintf(
                'Failed to restore %s for %s %s: %s',
                $context,
                $this->entityType(),
                (string) $entityId,
                $reason,
            ));
        }
        return $result;
    }

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------

    private function extractEntity(EventInterface $event): SoftDeletableInterface
    {
        $expected = $this->expectedEntityClass();
        $record = $event->getObject();

        if (!$record instanceof $expected) {
            throw new InvalidArgumentException(sprintf(
                '%s requires a %s on the event, got %s.',
                static::class,
                $expected,
                get_debug_type($record),
            ));
        }

        if (!$record instanceof SoftDeletableInterface) {
            throw new InvalidArgumentException(sprintf(
                '%s requires the entity to implement SoftDeletableInterface; %s does not.',
                static::class,
                $expected,
            ));
        }

        return $record;
    }

    private function extractEntityId(object $entity): int|string
    {
        $id = $entity->getEntityPrimaryKeyValue();

        if (is_int($id) && $id > 0) {
            return $id;
        }
        if (is_string($id) && $id !== '') {
            return $id;
        }
        if (is_numeric($id) && (int) $id > 0) {
            return (int) $id;
        }

        throw new InvalidArgumentException(sprintf(
            '%s requires a valid %s id.',
            static::class,
            $this->entityType(),
        ));
    }

    private function extractArchivedAt(SoftDeletableInterface $entity): DateTimeInterface
    {
        $archivedAt = $entity->getDeletedAt();

        if (!$archivedAt instanceof DateTimeInterface) {
            throw new InvalidArgumentException(sprintf(
                '%s requires the %s to still be archived (deleted_at must be set). '
                . 'Did the orchestrator restore the parent before dispatching the event?',
                static::class,
                $this->entityType(),
            ));
        }

        return $archivedAt;
    }

    /** @return array<string, mixed> */
    private function extractPayload(EventInterface $event): array
    {
        $payload = $event->getParams();
        return is_array($payload) ? $payload : [];
    }

    private function logFailure(Throwable $e, int|string $entityId, DateTimeInterface $archivedAt): void
    {
        $this->logger->error($e->getMessage(), [
            'listener' => static::class,
            'entity_type' => $this->entityType(),
            'entity_id' => $entityId,
            'archived_at' => $archivedAt->format(DateTimeInterface::ATOM),
            'exception' => $e,
        ]);
    }
}