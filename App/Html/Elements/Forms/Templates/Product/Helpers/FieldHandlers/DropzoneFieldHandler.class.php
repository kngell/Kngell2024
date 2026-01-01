<?php

declare(strict_types=1);

class DropzoneFieldHandler implements FieldHandlerInterface
{
    private const string TITLE_CLASS = 'input-box__media-title';
    private const array MEDIA_CLASS = ['input-box__media-upload', 'empty'];

    public function supports(string $fieldType): bool
    {
        return $fieldType === 'dropzone';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);

        return $form->tag('div')
            ->class(AbstractForm::INPUT_BOX)
            ->add(
                $form->tag('h6')
                    ->class(self::TITLE_CLASS)
                    ->content($field['title'] ?? ''),
                $form->tag('div')
                    ->class(...self::MEDIA_CLASS)
                    ->custom(['data-media-upload' => 'true'])
                    ->add(...$this->buildDropzoneElements($field, $form, $formInstance, $fieldId)),
            );
    }

    private function buildDropzoneElements(array $field, FormBuilder $form, AbstractForm $formInstance, string $fieldId): array
    {
        $mediaPreview = $this->createMediaPreview($form, $formInstance, $field);
        return [
            $mediaPreview,
            $form->input('file')
                ->class($field['input-class'] ?? '')
                ->id($fieldId)
                ->name($field['name'])
                ->accept($field['accept'] ?? '')
                ->multiple($field['multiple'] ?? false),
            $this->createMediaAvatar($field, $form, $formInstance),
            $form->tag('span')
                ->class('media-text')
                ->content($field['dragText'] ?? ''),
            $form->label()
                ->for($fieldId)
                ->class('btn', 'btn--secondary', 'btn--md-compact')
                ->add(
                    $form->tag('span')
                        ->class('btn__label')
                        ->content($field['buttonLabel'] ?? 'Add File'),
                ),
        ];
    }

    private function createMediaAvatar(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('media-avatar')
            ->add(
                $formInstance->createIcon($form, $field['icon'] ?? '', $field['icon-aria'] ?? ''),
            );
    }

    /**
     * @param FormBuilder $form
     * @param AbstractForm $formInstance
     * @param array $field
     *
     * @throws FormElementNotFound
     *
     * @return AbstractHtmlComponent
     */
    private function createMediaPreview(FormBuilder $form, AbstractForm $formInstance, array $field): AbstractHtmlComponent
    {
        $fieldName = $field['name'];
        $files = $formInstance->getFiles();
        $formErrors = $formInstance->getFormErrors();

        $previewContainer = $form->tag('div')
            ->class(...($field['preview-class'] ?? ['media-preview']));

        $fieldFiles = $this->getFilesForField($files, $fieldName);

        $validFiles = $this->filterValidFiles($fieldFiles, $formErrors, $fieldName);

        $previewItems = $this->buildPreviewItems($form, $formInstance, $field, $validFiles);

        if (!empty($previewItems)) {
            $previewContainer->add(...$previewItems);
            $previewContainer->removeClass('empty');
        }

        return $previewContainer;
    }

    private function getFilesForField(array $files, string $fieldName): array
    {
        $possibleKeys = [
            $fieldName,
            rtrim($fieldName, '[]'),
        ];

        foreach ($possibleKeys as $key) {
            if (isset($files[$key])) {
                return $files[$key];
            }
        }

        if (isset($files[0]) && is_array($files[0])) {
            $fieldFiles = [];
            foreach ($files as $file) {
                $intendedField = $file['intended_field'] ?? null;
                if ($intendedField === $fieldName || $intendedField === rtrim($fieldName, '[]')) {
                    $fieldFiles[] = $file;
                }
            }
            if (!empty($fieldFiles)) {
                return $fieldFiles;
            }
        }

        return [];
    }

    /**
     * @param FormBuilder $form
     * @param AbstractForm $formInstance
     * @param array $field
     * @param mixed $fieldFiles
     *
     * @return AbstractHtmlComponent[]
     */
    private function buildPreviewItems(FormBuilder $form, AbstractForm $formInstance, array $field, mixed $fieldFiles): array
    {
        $previewItems = [];

        if (!is_array($fieldFiles) || empty($fieldFiles)) {
            return $previewItems;
        }

        $files = isset($fieldFiles[0]) ? $fieldFiles : [$fieldFiles];

        foreach ($files as $fileInfo) {
            if (!is_array($fileInfo)) {
                continue;
            }

            $webPath = $fileInfo['web_path']
                    ?? $fileInfo['upload_infos']['web_path']
                    ?? $fileInfo['upload_infos']['url']
                    ?? null;

            if ($webPath) {
                $previewItems[] = $this->createPreviewItem($form, $formInstance, $field, $fileInfo, $webPath);
            }
        }

        return $previewItems;
    }

    /**
     * @param FormBuilder $form
     * @param AbstractForm $formInstance
     * @param array $field
     * @param array $fileInfo
     * @param string $webPath
     *
     * @return AbstractHtmlComponent
     */
    private function createPreviewItem(FormBuilder $form, AbstractForm $formInstance, array $field, array $fileInfo, string $webPath): AbstractHtmlComponent
    {
        $fileName = $fileInfo['display_name'] ?? $fileInfo['original_name'] ?? 'Unknown file';
        $fileSize = $fileInfo['size'] ?? 0;
        $fieldName = $field['name'];

        $hiddenInputName = 'web_path__' . $fieldName;
        if (str_ends_with($fieldName, '[]')) {
            $baseFieldName = rtrim($fieldName, '[]');
            $hiddenInputName = 'web_path__' . $baseFieldName . '[]';
        }

        return $form->tag('div')
            ->class('media-preview__item')
            ->add(
                $form->tag('div')
                    ->class('media-preview__item--img-container')
                    ->add(
                        $this->mediaType($form, $field, $fileInfo, $webPath),
                        $form->input('hidden')
                             ->name($hiddenInputName)
                             ->value($webPath),
                    ),
                $form->tag('div')
                    ->class('media-preview__item--icon-success')
                    ->add(
                        $formInstance->createIcon($form, 'icon-success', 'Success', ['success']),
                    ),
                $form->button('button')->type('button')
                    ->class('media-preview__item--icon-remove')
                    ->add(
                        $form->tag('span')
                            ->class('btn__icon')
                            ->add(
                                $formInstance->createIcon($form, 'icon-cancel', 'Cancel', ['cancel']),
                            ),
                    ),
                $form->tag('div')->class('media-preview__item--filename')->content($fileName),
                $form->tag('div')->class('media-preview__item--filesize')->content($this->formatFileSize($fileSize)),
            );
    }

    private function mediaType(FormBuilder $form, array $field, array $fileInfo, string $webPath): AbstractHtmlComponent
    {
        $mimeType = $fileInfo['mime_type'] ?? $fileInfo['metadata']['mime_type'] ?? '';
        $isImage = $fileInfo['is_image'] ?? $fileInfo['metadata']['is_image'] ?? false;
        $isVideo = $fileInfo['is_video'] ?? $fileInfo['metadata']['is_video'] ?? false;

        if ($isImage) {
            $container = $form->tag('img');
        } else {
            $container = $form->tag('video')->controls();
        }

        return $container->src($webPath)
            ->alt($field['alt'] ?? 'Product Image')
            ->class($field['img-class'] ?? 'image');
    }

    // DropzoneFieldHandler.php

    private function filterValidFiles(mixed $fieldFiles, array $formErrors, string $fieldName): array
    {
        $validFiles = [];
        $cleanFieldName = rtrim($fieldName, '[]');

        if (!is_array($fieldFiles)) {
            return $validFiles;
        }

        $files = [];
        foreach ($fieldFiles as $item) {
            if (is_array($item) && isset($item[0]) && is_array($item[0])) {
                $files = array_merge($files, $item);
            } else {
                $files[] = $item;
            }
        }
        foreach ($files as $fileInfo) {
            if (!is_array($fileInfo)) {
                continue;
            }

            $intendedField = $fileInfo['intended_field'] ?? null;
            if ($intendedField && $intendedField !== $cleanFieldName) {
                continue;
            }

            $hasFileError = $fileInfo['has_error'] ?? false;
            $isWebPath = $fileInfo['is_from_web_path'] ?? false;
            $webPathExists = !empty($fileInfo['web_path'] ?? null);

            if (!$hasFileError || $isWebPath || $webPathExists) {
                $validFiles[] = $fileInfo;
            }
        }

        return $validFiles;
    }

    /**
     * Check if a single file is valid.
     */
    private function isValidFile(array $fileData, array $formErrors, string $fieldName): bool
    {
        $fileName = $fileData['original_name'] ?? $fileData['name'] ?? '';

        // Check for errors in multiple locations
        $hasFileError = $fileData['has_error'] ?? false;
        $hasFormError = $this->hasFormErrorForFile($formErrors, $fieldName, $fileName);

        // Only include files without errors from both sources
        return !$hasFileError && !$hasFormError;
    }

    private function hasFormErrorForFile(array $formErrors, string $fieldName, string $fileName): bool
    {
        $cleanFieldName = rtrim($fieldName, '[]');
        return array_key_exists($cleanFieldName, $formErrors);
    }

    /**
     * Check if file is a video based on MIME type or extension.
     */
    private function isVideoFile(?string $mimeType, string $fileName): bool
    {
        $videoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];

        if ($mimeType && in_array($mimeType, $videoMimes)) {
            return true;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions);
    }

    /**
     * Format file size for display.
     */
    private function formatFileSize(int $size): string
    {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}