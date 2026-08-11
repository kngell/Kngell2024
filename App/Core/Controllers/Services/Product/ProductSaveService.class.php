<?php

declare(strict_types=1);

final class ProductSaveService extends AbstractSaveService
{
    public function __construct(
        private ProductModel $model,
    ) {
    }

    public function getValidationRules(): string
    {
        return 'productRules';
    }

    public function getEntityName(): string
    {
        return Product::class;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getEditUrl(int $entityId): string
    {
        $entityName = $this->getEntityName();
        return "/admin/{$entityId}/{$entityName}-edit";
    }

    public function getAddUrl(): string
    {
        $entityName = $this->getEntityName();
        return "/admin/{$entityName}-add";
    }

    public function processFilePaths(
        array $formData,
        FileUploadCompositeInterface $uploadService,
    ): array {
        return [
            'main_image' => $uploadService->getFilePath('main_image'),
            'main_video' => $uploadService->getFilePath('main_video'),
            'img_gallery' => $uploadService->getMultiFilePaths('img_gallery'),
        ];
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        return isset($formData['pdt_id']) ? (int) $formData['pdt_id'] : null;
    }

    #[Override]
    public function buildSaveEvent(EventDataDTO $eventData): AbstractEvent
    {
        return new ProductEvent($eventData);
    }
}