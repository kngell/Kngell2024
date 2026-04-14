<?php

declare(strict_types=1);

class SmallBannerSaveService implements SaveServiceInterface
{
    public function __construct(
        private SmallBannerModel $smallBannerModel,
    ) {
    }

    public function getValidationRules(): string
    {
        return 'smallBannerRules';
    }

    public function getModel(): Model
    {
        return $this->smallBannerModel;
    }

    public function getEntityName(): string
    {
        return SmallBanner::class;
    }

    public function getEventClass(): string
    {
        return SmallBannerEvent::class;
    }

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array
    {
        return [
            'custom_image_url' => $uploadService->getFilePath('custom_image_url'),
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
            'small_banner_id' => $entityId,
            'operation' => $operationType,
            'was_skipped' => $wasSkipped,
            'form_data' => $formData,
            'media' => $filePaths,
            'model_data' => $modelData,
            'context' => [
                'is_new_entity' => ($operationType === 'insert'),
            ],
        ];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if (($operationType === 'insert' || $operationType === 'update') && $entityId) {
            return "/small-banner-page/{$entityId}/edit";
        }

        return '';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        return match($operationType) {
            'insert' => 'The Small Banner section has been created successfully',
            'update' => !$wasSkipped
                ? 'The Small Banner section has been updated successfully'
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