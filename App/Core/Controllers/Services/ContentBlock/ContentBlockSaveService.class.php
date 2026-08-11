<?php

declare(strict_types=1);

class ContentBlockSaveService extends AbstractSaveService
{
    private ?BlockType $blockType = null;

    public function __construct(
        private ContentBlockModel $heroModel,
    ) {
    }

    #[Override]
    public function buildSaveEvent(EventDataDTO $eventData): AbstractEvent
    {
        return new ContentBlockEvent($eventData);
    }

    #[Override]
    public function getAddUrl(?string $blockType = null): string
    {
        return match ($blockType) {
            BlockType::HERO->value => HeroSectionLinks::ADD->value ,
        };
    }

    #[Override]
    public function getEditUrl(int $entityId): string
    {
        if (!$this->blockType) {
            return '';
        }
        return ContentBlockLinks::getEditRoute($this->blockType, (string) $entityId);
        // return "/admin/content-block-page/{$entityId}/edit";
    }

    public function getValidationRules(): string
    {
        return 'contentBlockRules';
    }

    public function getModel(): Model
    {
        return $this->heroModel;
    }

    public function getEntityName(): string
    {
        return ContentBlock::class;
    }

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array
    {
        $fieldsNames = $uploadService->getAllFieldsName();
        $imagesUrls = [];

        foreach ($fieldsNames as $fieldName) {
            $file = $uploadService->getFilePath($fieldName);

            if ($file === null) {
                continue;
            }
            $this->setNestedValue($imagesUrls, $fieldName, $file);
        }

        return $imagesUrls;
    }

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string
    {
        if (($operationType === 'INSERT' || $operationType === 'UPDATE') && $entityId) {
            return $this->getEditUrl($entityId);
        }
        return '';
    }

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string
    {
        $pageTitle = $this->blockType?->getPageTitle() ?? 'Content Block';
        return match($operationType) {
            'INSERT' => "The $pageTitle has been created successfully",
            'UPDATE' => !$wasSkipped
                ? "The $pageTitle has been updated successfully"
                : "No changes were made to the $pageTitle",
            default => 'Content block operation completed',
        };
    }

    public function getEntityIdFromForm(array $formData): ?int
    {
        // If hero has an ID field
        return isset($formData['id']) ? (int) $formData['id'] : null;
    }

    public function setBlockType(?BlockType $blockType = null): void
    {
        $this->blockType = $blockType;
    }

    private function setNestedValue(array &$array, string $path, mixed $value): void
    {
        // Parse bracket notation: block_metadata[image][top_left]
        if (preg_match_all('/\[([^\]]+)\]/', $path, $matches)) {
            $keys = $matches[1];

            // Also handle the part before the first bracket
            if (str_contains($path, '[')) {
                $mainKey = substr($path, 0, strpos($path, '['));
                array_unshift($keys, $mainKey);
            }

            $current = &$array;
            foreach ($keys as $key) {
                if (!isset($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }

            // Handle array values (like when multiple files)
            if (is_array($value) && count($value) === 1) {
                $current = $value[0];
            } elseif (is_array($value)) {
                $current = $value;
            } else {
                $current = $value;
            }
        } else {
            // Simple key without brackets
            $array[$path] = $value;
        }
    }
}