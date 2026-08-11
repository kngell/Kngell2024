<?php

declare(strict_types=1);

interface SaveServiceInterface
{
    public function getValidationRules(): string;

    public function getModel(): Model;

    public function getEntityName(): string;

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array;

    public function buildSaveEvent(EventDataDTO $eventData): AbstractEvent;

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string;

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string;

    public function getEntityIdFromForm(array $formData): ?int;

    public function getErrorRedirectUrl(array $formData): string;

    public function setBlockType(?BlockType $blockType = null): void;
}