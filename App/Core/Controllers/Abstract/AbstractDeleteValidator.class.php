<?php

declare(strict_types=1);

abstract class AbstractDeleteValidator
{
    public function validate(string $id): DeletionValidatorResult
    {
        $result = new DeletionValidatorResult();

        if (empty($id)) {
            $result->addError($this->getLabel() . ' ID is required.');

            return $result;
        }

        $record = $this->findRecord($id);

        if (!$record) {
            $result->addError($this->getLabel() . ' not found.');

            return $result;
        }

        $result->setRecord($record);
        $result->setDisplayName($this->resolveDisplayName($record));
        $result->setDisplayImage($this->resolveDisplayImage($record));

        if ($record->hasSoftDelete() && $record->isDeleted()) {
            $result->addWarning(
                $this->getLabel()
                . ' is already deleted. This operation will have no effect.',
            );
            $result->setSoftDeleted(true);
        }

        $this->populateMetadata($record, $result);
        $this->checkBusinessRules($id, $record, $result);

        return $result;
    }

    public function getEntityKeyfield(): ?string
    {
        return null;
    }

    abstract protected function getLabel(): string;

    abstract protected function findRecord(string $id): ?object;

    abstract protected function resolveDisplayName(Entity $record): string;

    protected function resolveDisplayImage(Entity $record): ?string
    {
        return null;
    }

    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
    }

    protected function checkBusinessRules(
        string $id,
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
    }
}