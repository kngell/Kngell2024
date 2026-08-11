<?php

declare(strict_types=1);

abstract class AbstractDeleteService
{
    // ✅ Clean signature — no dispatcher parameter
    public function delete(
        array $flashData,
        string $deleteOption = 'archive',
        ?string $blockType = null,
    ): DeleteResult {
        /** @var Entity */
        $record = $this->findRecord($flashData['id']);

        if (!$record) {
            return DeleteResult::failure(
                errorMessage: $this->getLabel() . ' not found.',
                errorDetails: [
                    'id' => $flashData['id'],
                ],
            );
        }

        if ($this->isRecordDeleted($record) && $deleteOption === 'archive') {
            return DeleteResult::success(
                id:            $flashData['id'],
                name:          $this->resolveDisplayName($record),
                affectedRows:  0,
                wasSkipped:    true,
                skipReason:    $this->getLabel() . ' was already archived',
                isSoftDeleted: true,
                deleteOption:  'archive',
            );
        }

        return $this->executeDelete(
            $flashData,
            $deleteOption,
            $record,
            $blockType,
        );
    }

    protected function getEventDispatcher(): ?EventDispatcherInterface
    {
        return null;
    }

    // --- Abstract contracts ---
    abstract protected function findRecord(array $id): ?object;

    abstract protected function getLabel(): string;

    abstract protected function getEventName(): string;

    abstract protected function resolveDisplayName(Entity $record): ?string;

    abstract protected function performDelete(Entity $entity, string $deleteOption): QueryResult;

    abstract protected function getEntityManager(): mixed;

    abstract protected function clearModelState(): void;

    abstract protected function buildDeletionEvent(
        EventDataDTO $dto,
    ): AbstractEvent;

    protected function getEntityId(Entity $record, array $flashData): mixed
    {
        return $record->getEntityPrimarykeyValue();
    }

    protected function isRecordDeleted(object $record): bool
    {
        if ($record instanceof SoftDeletableInterface) {
            return $record->isDeleted();
        }
        return false;
    }

    // --- Execution ---

    private function executeDelete(
        array $flashData,
        string $deleteOption,
        Entity $record,
        ?string $blockType = null,
    ): DeleteResult {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            $this->clearModelState();
            $modelResult = $this->performDelete($record, $deleteOption);

            if (!$modelResult->isSuccess()) {
                $em->rollback();
                $this->clearModelState();

                return DeleteResult::failure(
                    errorMessage: $this->getLabel() . ' deletion failed.',
                    errorDetails: [
                        'id' => $flashData['id'],
                        'skip_reason' => $modelResult->getSkipReason(),
                        'deleteOption' => $deleteOption,
                    ],
                );
            }

            if ($modelResult->wasSkipped()) {
                $em->rollback();
                $this->clearModelState();

                return DeleteResult::success(
                    id: $flashData['id'],
                    name: $this->resolveDisplayName($record),
                    affectedRows: 0,
                    wasSkipped: true,
                    skipReason: $modelResult->getSkipReason(),
                    isSoftDeleted: $this->isRecordDeleted($record),
                    deleteOption: $deleteOption,
                );
            }
            $event = null;
            if ($modelResult->getAffectedRows() > 0) {
                $event = $this->dispatchDeletionEvent(
                    $flashData,
                    $record,
                    $deleteOption,
                    $modelResult,
                    $blockType,
                );
            }
            $this->clearModelState();
            if ($event === null) {
                $em->rollback();
                return DeleteResult::failure(
                    errorMessage: 'Failed to delete ' . strtolower($this->getLabel()),
                    errorDetails: [
                        'id' => $flashData['id'],
                        'deletionOption' => $deleteOption,
                        'operation' => $modelResult->getSqlOperation()->value,
                    ],
                );
            }
            $em->commit();

            return DeleteResult::success(
                id:            $flashData['id'],
                name:          $this->resolveDisplayName($record),
                affectedRows:  $modelResult->getAffectedRows(),
                wasSkipped:    false,
                skipReason:    '',
                isSoftDeleted: ($deleteOption === 'archive'),
                deleteOption:  $deleteOption,
                operation: 'DELETE',
            );
        } catch (Exception $e) {
            $em->rollback();
            $this->clearModelState();
            $code = $e->getCode();
            if ($e->getCode() === '23000') {
                $code = 409;
            }

            return DeleteResult::failure(
                errorMessage: 'Failed to delete ' . strtolower($this->getLabel()) . ': ' . $e->getMessage(),
                errorDetails: [
                    'id' => $flashData['id'],
                    'exception' => $e->getTraceAsString(),
                    'code' => $code,
                    'deletionOption' => $deleteOption,
                ],
            );
        }
    }

    private function dispatchDeletionEvent(
        array $flashData,
        object $record,
        string $deleteOption,
        QueryResult $modelResult,
        ?string $blockType = null,
    ): ?EventInterface {
        $dispatcher = $this->getEventDispatcher();

        if ($dispatcher === null) {
            return null;
        }

        $idValue = $flashData['id']['value'] ?? '';

        $publicId = StringUtils::isUuid((string) $idValue) ? $idValue : null;

        $keyId = $this->isValidNumericId($idValue) ? (int) $idValue : null;
        $entityId = $this->getEntityId($record, $flashData);

        $event = $this->buildDeletionEvent(
            EventDataDTO::from(
                eventName: $this->getEventName(),
                entityId: $entityId,
                record: $record,
                identifier: $flashData['id'],
                deleteOption: $deleteOption,
                publicId: $publicId,
                operation: $modelResult->getSqlOperation()->value,
                wasSkipped: $modelResult->wasSkipped(),
                blockType: $blockType,
                keyId: $keyId,
            ),
        );

        return $dispatcher->notify($event);
    }

    private function isValidNumericId(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
            ],
        ]);

        return $filtered !== false;
    }
}