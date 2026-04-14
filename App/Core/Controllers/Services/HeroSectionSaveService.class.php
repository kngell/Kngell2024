<?php

declare(strict_types=1);

class HeroSectionSaveService implements SaveServiceInterface
{
    public function __construct(
        private HeroModel $heroModel,
    ) {
    }

    public function getValidationRules(): string
    {
        return 'heroRules';
    }

    public function getModel(): Model
    {
        return $this->heroModel;
    }

    public function getEntityName(): string
    {
        return 'hero';
    }

    public function getEventClass(): string
    {
        return HeroSectionEvent::class;
    }

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array
    {
        return [
            'image_url' => $uploadService->getFilePath('image_url'),
        ];
    }

    public function buildEventData(
        array $formData,
        array $filePaths,
        string $operationType,
        int $entityId,
        bool $wasSkipped,
        array $modelData = [],
    ): array {
        return [
            'hero_id' => $entityId,
            'operation' => $operationType,
            'was_skipped' => $wasSkipped,
            'form_data' => $formData,
            'media' => $filePaths,
            'model_data' => $modelData,
            'context' => [
                'is_new_hero' => ($operationType === 'insert'),
            ],
        ];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if (($operationType === 'insert' || $operationType === 'update') && $entityId) {
            return "/hero-page/{$entityId}/hero-edit";
        }

        return '';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        return match($operationType) {
            'insert' => 'The hero section has been created successfully',
            'update' => !$wasSkipped
                ? 'The hero section has been updated successfully'
                : 'No changes were made to the hero section',
            default => 'Hero section operation completed',
        };
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        // If hero has an ID field
        return isset($formData['hero_id']) ? (int) $formData['hero_id'] : null;
    }
}