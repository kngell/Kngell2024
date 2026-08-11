<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractEntityDeletionListener implements EventListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    final public function handle(EventInterface $event): DeletionResultInterface
    {
        $entityId = $this->extractEntityId($event);
        $payload = $this->extractPayload($event);
        $deletionOption = $this->resolveDeletionOption($payload);

        try {
            return $this->performDeletion($entityId, $deletionOption, $payload);
        } catch (RuntimeException $e) {
            $this->logFailure($e, $entityId, $deletionOption);
            throw $e;
        } catch (Throwable $e) {
            $this->logFailure($e, $entityId, $deletionOption);
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

    /**
     * Short, stable identifier for the entity type, e.g. 'product', 'hero'.
     */
    abstract protected function entityType(): string;

    /**
     * @param array<string, mixed> $payload
     */
    abstract protected function performDeletion(
        int|string $entityId,
        string $deletionOption,
        array $payload,
    ): DeletionResultInterface;

    protected function defaultDeletionOption(): string
    {
        return 'permanent';
    }

    protected function assertSuccess(
        QueryResult $result,
        string $context,
        int|string $entityId,
    ): object {
        if (!$result->isSuccess()) {
            $reason = method_exists($result, 'getErrorMessage')
                ? $result->getErrorMessage()
                : 'unknown error';

            throw new RuntimeException(sprintf(
                'Failed to delete %s for %s %s: %s',
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

    private function extractEntityId(EventInterface $event): int|string
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

        $id = $record->getEntityPrimaryKeyValue();

        if (is_int($id) && $id > 0) {
            return $id;
        }
        if (is_string($id) && $id !== '') {
            return $id;
        }
        // Numeric strings → int (legacy hydration)
        if (is_numeric($id) && (int) $id > 0) {
            return (int) $id;
        }

        throw new InvalidArgumentException(sprintf(
            '%s requires a valid %s id.',
            static::class,
            $this->entityType(),
        ));
    }

    /** @return array<string, mixed> */
    private function extractPayload(EventInterface $event): array
    {
        $payload = $event->getParams();
        return is_array($payload) ? $payload : [];
    }

    /** @param array<string, mixed> $payload */
    private function resolveDeletionOption(array $payload): string
    {
        $option = $payload['deletion_option'] ?? null;
        return (is_string($option) && $option !== '')
            ? $option
            : $this->defaultDeletionOption();
    }

    private function logFailure(Throwable $e, int|string $entityId, string $mode): void
    {
        $this->logger->error($e->getMessage(), [
            'listener' => static::class,
            'entity_type' => $this->entityType(),
            'entity_id' => $entityId,
            'mode' => $mode,
            'exception' => $e,
        ]);
    }
}