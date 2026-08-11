<?php

declare(strict_types=1);

abstract class AbstractSaveService implements SaveServiceInterface
{
    abstract public function getModel(): Model;

    abstract public function getValidationRules(): string;

    abstract public function getEntityName(): string;

    abstract public function buildSaveEvent(EventDataDTO $eventData): AbstractEvent;

    public function processFilePaths(
        array $formData,
        FileUploadCompositeInterface $uploadService,
    ): array {
        return [];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        $operationType = strtolower($operationType);

        if (in_array($operationType, ['insert', 'update']) && $entityId) {
            return $this->getEditUrl($entityId);
        }

        return '';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        $entity = ucfirst($this->getEntityName());
        $operationType = strtolower($operationType);

        return match ($operationType) {
            'insert' => "The {$entity} has been created successfully",
            'update' => !$wasSkipped
                ? "The {$entity} has been updated successfully"
                : "No changes were made to the {$this->getEntityName()}",
            default => "{$entity} operation completed",
        };
    }

    public function getErrorRedirectUrl(array $formData): string
    {
        $entityId = $this->getEntityIdFromForm($formData);

        if ($entityId) {
            return $this->getEditUrl($entityId);
        }
        return $this->getAddUrl();
    }

    public function setBlockType(?BlockType $blockType = null): void
    {
    }

    abstract protected function getAddUrl(): string;

    abstract protected function getEditUrl(int $entityId): string;
}