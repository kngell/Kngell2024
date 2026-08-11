<?php

declare(strict_types=1);

abstract class AbstractDropzoneHandler implements DropzoneHandlerInterface
{
    use FileTrimTrait;

    public function __construct(
        protected readonly IconBuilder $iconBuilder,
        protected readonly ?FileMetadataService $fileMetadataService = null,
    ) {
    }

    abstract public function getName(): string;

    abstract public function renderEmpty(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        string $fieldId,
    ): AbstractHtmlComponent;

    abstract public function renderPopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent;

    /**
     * Get processed files with metadata.
     */
    protected function getProcessedFiles(array $field, AbstractForm $formInstance): array
    {
        $files = $formInstance->getFiles($field['name']);

        if (empty($files)) {
            return [];
        }

        $processed = [];
        $fieldName = rtrim($field['name'], '[]');

        foreach ($files as $file) {
            if (is_array($file) && isset($file['web_path'])) {
                $processed[] = $file;
                continue;
            }

            if ($file instanceof FileInformation && $this->fileMetadataService) {
                $metadata = $this->fileMetadataService->getUploadMetadata($file, $fieldName);
                $processed[] = $metadata;
            } elseif (is_string($file) && file_exists($file) && $this->fileMetadataService) {
                try {
                    $fileInfo = new FileInformation($file);
                    $metadata = $this->fileMetadataService->getUploadMetadata($fileInfo, $fieldName);
                    $processed[] = $metadata;
                } catch (Throwable $e) {
                    continue;
                }
            }
        }

        return $processed;
    }

    protected function createHiddenInputs(
        FormBuilder $form,
        array $files,
        array $field,
        bool $isMultiple,
    ): AbstractHtmlComponent {
        $container = $form->tag('div')->class('media-hidden-inputs');
        $baseFieldName = $field['name'];

        if (substr($baseFieldName, -2) === '[]') {
            $baseFieldName = substr($baseFieldName, 0, -2);
        }

        $hiddenFieldName = FormValuesKeys::WEBPATH_PREFIX->value . $baseFieldName;

        // Add array syntax for multiple files
        if ($isMultiple) {
            $hiddenFieldName .= '[]';
        }

        foreach ($files as $file) {
            $webPath = $file['web_path'] ?? '';
            if (empty($webPath)) {
                continue;
            }

            $hiddenInput = $form->input('hidden')
                ->name($hiddenFieldName)
                ->value($webPath)
                ->custom([
                    'data-webpath' => $webPath,
                    'data-preserve' => 'true',
                    'data-base-field' => $baseFieldName,
                ]);

            $container->add($hiddenInput);
        }

        return $container;
    }

    protected function isMultiple(array $field): bool
    {
        return $field['multiple'] ?? false;
    }

    protected function formatFileSize(int $size): string
    {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($size, 1024));
        $power = min($power, count($units) - 1);

        return number_format($size / pow(1024, $power), 1) . ' ' . $units[$power];
    }

    /**
     * Create file icon based on file type.
     */
    protected function createFileIcon(FormBuilder $form, array $file): AbstractHtmlComponent
    {
        $icon = match ($file['file_type'] ?? '') {
            'Video File' => 'icon-video',
            'Audio File' => 'icon-audio',
            'Document File' => 'icon-document',
            'Archive File' => 'icon-archive',
            default => 'icon-file',
        };

        return $this->iconBuilder->createIcon($icon, 'File');
    }

    protected function createMediaElement(FormBuilder $form, array $field, array $file, string $webPath): AbstractHtmlComponent
    {
        $isImage = $file['is_image'] ?? false;
        $isVideo = $file['is_video'] ?? false;

        if ($isImage) {
            return $form->tag('img')
                ->src($webPath)
                ->alt($field['alt'] ?? 'Preview')
                ->class($field['img-class'] ?? 'upload-preview__image');
        }

        if ($isVideo) {
            return $form->tag('video')
                ->src($webPath)
                ->controls()
                ->class($field['video-class'] ?? 'video-preview');
        }

        return $this->createFileIcon($form, $file);
    }
}