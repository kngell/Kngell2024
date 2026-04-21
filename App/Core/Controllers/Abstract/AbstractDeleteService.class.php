<?php

declare(strict_types=1);

abstract class AbstractDeleteService
{
    public function delete(
        string $id,
        string $deleteOption = 'archive',
        ?EventManagerInterface $eventManager = null,
    ): DeleteResult {
        $record = $this->findRecord($id);

        if (!$record) {
            return DeleteResult::failure(
                $this->getLabel() . ' not found.',
                ['id' => $id],
            );
        }

        if ($this->isRecordDeleted($record) && $deleteOption === 'archive') {
            return DeleteResult::success(
                id: $id,
                name: $this->resolveDisplayName($record),
                affectedRows: 0,
                wasSkipped: true,
                skipReason: $this->getLabel() . ' was already archived',
                isSoftDeleted: true,
                deletionType: 'archive',
            );
        }

        $eventData = $this->buildEventData($record);

        return $this->executeDelete(
            $id,
            $deleteOption,
            $record,
            $eventData,
            $eventManager,
        );
    }

    public function getEntityKeyfield(): ?string
    {
        return null;
    }

    // --- Abstract contracts ---

    abstract protected function findRecord(string $id): ?object;

    abstract protected function getLabel(): string;

    abstract protected function getEventName(): string;

    abstract protected function getEventClassName(): ?string;

    abstract protected function resolveDisplayName(object $record): string;

    abstract protected function performDelete(
        string $id,
        string $deleteOption,
    ): mixed;

    abstract protected function getEntityManager(): mixed;

    abstract protected function clearModelState(): void;

    abstract protected function isRecordDeleted(object $record): bool;

    abstract protected function buildEventData(Entity $record): array;

    // --- Private execution ---

    private function executeDelete(
        string $id,
        string $deleteOption,
        object $record,
        array $eventData,
        ?EventManagerInterface $eventManager,
    ): DeleteResult {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            $this->clearModelState();
            $modelResult = $this->performDelete($id, $deleteOption);

            if (!$modelResult->isSuccess()) {
                $em->rollback();
                $this->clearModelState();

                return DeleteResult::failure(
                    $this->getLabel() . ' deletion failed.',
                    [
                        'id' => $id,
                        'skip_reason' => $modelResult->getSkipReason(),
                        'deletion_type' => $deleteOption,
                    ],
                );
            }

            if ($modelResult->wasSkipped()) {
                $em->rollback();
                $this->clearModelState();

                return DeleteResult::success(
                    id: $id,
                    name: $this->resolveDisplayName($record),
                    affectedRows: 0,
                    wasSkipped: true,
                    skipReason: $modelResult->getSkipReason(),
                    isSoftDeleted: $this->isRecordDeleted($record),
                    deletionType: $deleteOption,
                );
            }

            if (
                $modelResult->getAffectedRows() > 0
                && $eventManager !== null
            ) {
                $this->dispatchDeletionEvent(
                    $id,
                    $eventData,
                    $record,
                    $eventManager,
                    $deleteOption,
                );
            }

            $em->commit();
            $this->clearModelState();

            return DeleteResult::success(
                id: $id,
                name: $this->resolveDisplayName($record),
                affectedRows: $modelResult->getAffectedRows(),
                wasSkipped: false,
                skipReason: '',
                isSoftDeleted: ($deleteOption === 'archive'),
                deletionType: $deleteOption,
            );
        } catch (Exception $e) {
            $em->rollback();
            $this->clearModelState();

            return DeleteResult::failure(
                'Failed to delete '
                    . strtolower($this->getLabel()) . ': '
                    . $e->getMessage(),
                [
                    'id' => $id,
                    'exception' => $e->getTraceAsString(),
                    'deletion_type' => $deleteOption,
                ],
            );
        }
    }

    private function dispatchDeletionEvent(
        string $id,
        array $eventData,
        object $record,
        EventManagerInterface $eventManager,
        string $deleteOption,
    ): void {
        $deletionType = ($deleteOption === 'permanent')
            ? 'permanent'
            : 'soft';

        $eventClass = $this->getEventClassName();

        $eventManager->notify(
            new $eventClass($this->getEventName(), null, [
                'id' => $id,
                'public_id' => $eventData['public_id'] ?? null,
                'data' => $eventData,
                'record' => $record,
                'deletion_type' => $deletionType,
                'deletion_option' => $deleteOption,
                'timestamp' => time(),
            ]),
            null,
        );
    }
}