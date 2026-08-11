<?php

declare(strict_types=1);
class FooterSocialSaveService extends AbstractSaveService
{
    public function __construct(
        private FooterSocialModel $model,
    ) {
    }

    #[Override]
    public function buildSaveEvent(EventDataDTO $eventData): AbstractEvent
    {
        return new FooterSocialEvent($eventData);
    }

    public function getEditUrl(int $entityId): string
    {
        return "/admin/footer-social/{$entityId}/edit";
    }

    public function getValidationRules(): string
    {
        return 'footerSocialRules';
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getEntityName(): string
    {
        return FooterSocial::class;
    }

    public function getEventClass(): string
    {
        return FooterSocialEvent::class;
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
            'entity_id' => $entityId,
            'operation' => $operationType,
            'was_skipped' => $wasSkipped,
            'form_data' => $formData,
            'media' => $filePaths,
            'model_data' => $modelData,
            'context' => [
                'is_new' => ($operationType === 'insert'),
                'parent_id' => $formData['parent_id'] ?? null,
                'level' => $formData['level'] ?? 0,
            ],
        ];
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if (($operationType === SqlStatement::INSERT->value || $operationType === SqlStatement::UPDATE->value) && $entityId) {
            return '/admin/footer-page/index';
        }

        return '/admin/footer-page/index';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        return match($operationType) {
            'insert' => 'Footer Socials has been created successfully',
            'update' => !$wasSkipped
                ? 'Footer Socials has been updated successfully'
                : 'No changes were made to the Footer Socials',
            'delete' => 'Footer Socials has been deleted successfully',
            default => 'Footer operation completed',
        };
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        return isset($formData['id']) ? (int) $formData['id'] : null;
    }

    public function updateOrderIndex(array $categoryOrder): bool
    {
        foreach ($categoryOrder as $index => $categoryId) {
            $result = $this->model->update($categoryId, ['order_index' => $index]);

            if (!$result->isSuccess()) {
                return false;
            }
        }

        return true;
    }

    public function toggleActive(int $id, bool $isActive): bool
    {
        $result = $this->model->update($id, ['is_active' => $isActive]);
        return $result->isSuccess();
    }

    protected function getAddUrl(): string
    {
        return '/admin/footer-about/add';
    }
}