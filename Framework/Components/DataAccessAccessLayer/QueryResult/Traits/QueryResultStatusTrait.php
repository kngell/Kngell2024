<?php

declare(strict_types=1);
trait QueryResultStatusTrait
{
    public function isSuccess(): bool
    {
        if ($this->wasSkipped()) {
            return true;
        }

        return $this->executionStatus !== false;
    }

    public function hasChanged(): bool
    {
        if ($this->wasSkipped()) {
            return false;
        }
        return $this->isSuccess() && $this->rowCount > 0;
    }

    public function wasSuccessful(): bool
    {
        return $this->isSuccess();
    }

    public function getAffectedRows(): int
    {
        if ($this->wasSkipped()) {
            return 0;
        }
        return $this->rowCount;
    }

    public function queryExecuted(): bool
    {
        return $this->dataMapper->getExecutionStatus() !== false;
    }

    public function isInsertOperation(): bool
    {
        return $this->statementType === SqlStatement::INSERT;
    }

    public function isUpdateOperation(): bool
    {
        return $this->statementType === SqlStatement::UPDATE;
    }

    public function wasSkipped(): bool
    {
        return $this->isSkipped;
    }

    public function wasEffectivelyUpdated(): bool
    {
        return $this->isSuccess() && ($this->rowCount > 0 || $this->wasSkipped());
    }
}