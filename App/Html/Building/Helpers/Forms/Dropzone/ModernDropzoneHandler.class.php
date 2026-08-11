<?php

declare(strict_types=1);

class ModernDropzoneHandler extends AbstractDropzoneHandler
{
    private array $classConfig;

    public function __construct(
        IconBuilder $iconBuilder,
        ?FileMetadataService $fileMetadataService = null,
        array $classConfig = [],
    ) {
        parent::__construct($iconBuilder, $fileMetadataService);

        $this->classConfig = array_merge([
            'single-class' => 'upload-single',
            'multiple-class' => 'upload-multiple',
        ], $classConfig);
    }

    public function getName(): string
    {
        return 'modern';
    }

    public function renderEmpty(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        string $fieldId,
    ): AbstractHtmlComponent {
        $multiple = $field['multiple'] ?? false;
        $baseClass = $multiple
            ? $this->classConfig['multiple-class']
            : $this->classConfig['single-class'];

        // Build the dropzone HTML structure
        $dropzone = $form->tag('div')
            ->class($baseClass)
            ->custom(['data-state' => 'empty']);
        if ($multiple) {
            $dropzone->attribute('data-mode', 'multiple');
        }

        // Icon
        $dropzone->add(
            $form->tag('div')->class($baseClass . '__icon')->add(
                $this->iconBuilder->createIcon(
                    $field['icon'] ?? 'icon-upload',
                    $field['icon-aria'] ?? 'Upload',
                ),
            ),
        );

        // Text container
        $textContainer = $form->tag('div')->class($baseClass . '__text');

        // Main text
        $textContainer->add(
            $form->tag('span')
                ->class($baseClass . '__main-text')
                ->content($field['dragText'] ?? ($multiple
                    ? 'Drag & drop files or click to upload'
                    : 'Drag & drop or click to upload')),
        );

        // Hint text
        $textContainer->add(
            $form->tag('span')
                ->class($baseClass . '__hint-text')
                ->content($field['hintText'] ?? ($multiple
                    ? 'PNG, JPG, GIF • Max 5MB each'
                    : 'PNG, JPG, GIF • Max 5MB')),
        );

        $dropzone->add($textContainer);

        $fieldName = $field['name'];
        if ($multiple && !str_ends_with($fieldName, '[]')) {
            $fieldName = $fieldName . '[]';
        }

        // File input
        $input = $form->input('file')
            ->id($fieldId)
            ->name($fieldName)
            ->accept($field['accept'] ?? 'image/*');

        if ($multiple) {
            $input->attribute('multiple', 'true');
        }

        $dropzone->add($input);

        return $dropzone;
    }

    public function renderPopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent {
        $multiple = $field['multiple'] ?? false;

        if ($multiple) {
            return $this->renderMultiplePopulated($field, $form, $files, $fieldId);
        }

        return $this->renderSinglePopulated($field, $form, $files, $fieldId);
    }

    /**
     * Render single dropzone with preview (matches JS SinglePreviewDropzone).
     */
    private function renderSinglePopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent {
        $baseClass = $this->classConfig['single-class'];
        $previewClass = $baseClass . ' ' . $baseClass . '--preview';

        // Take only the first file for single mode
        $file = $files[0] ?? null;

        $dropzone = $form->tag('div')
            ->class($previewClass)
            ->custom([
                'data-state' => 'preview',
                'data-mode' => 'single',
            ]);

        // ✅ Previews grid (single item)
        $previewsGrid = $form->tag('div')->class($baseClass . '__preview-container');
        $previewItem = $form->tag('div')->class($baseClass . '__preview-item');

        // ✅ Preview container (matches JS structure)
        $previewContainer = $form->tag('div')->class($baseClass . '__preview-container');
        $preview = $form->tag('div')->class($baseClass . '__preview');

        $isImage = $file['is_image'] ?? false;
        if ($isImage) {
            $preview->add(
                $form->tag('img')
                    ->src($file['web_path'] ?? '')
                    ->alt($field['alt'] ?? 'Preview'),
            );
        } else {
            $preview->add($this->createFileIcon($form, $file));
        }

        $previewContainer->add($preview);
        $previewItem->add($previewContainer);

        // ✅ Content section (info + actions together, matching JS)
        $content = $form->tag('div')->class($baseClass . '__content');

        // Info
        $info = $form->tag('div')->class($baseClass . '__info');
        $info->add(
            $form->tag('span')->class($baseClass . '__filename')
                ->content($file['display_name'] ?? $file['original_name'] ?? 'File'),
            $form->tag('span')->class($baseClass . '__filesize')
                ->content($file['formatted_size'] ?? $this->formatFileSize($file['size'] ?? 0)),
        );
        $content->add($info);

        // Actions (remove button)
        $actions = $form->tag('div')->class($baseClass . '__actions');
        $actions->add(
            $form->button('button')
                ->class('remove')
                ->content('Remove'),
        );
        $content->add($actions);

        $previewItem->add($content);
        $previewsGrid->add($previewItem);
        $dropzone->add($previewsGrid);

        // ✅ Hidden file input (for replacement)
        $fieldName = $field['name'];
        $input = $form->input('file')
            ->id($fieldId)
            ->name($fieldName)
            ->accept($field['accept'] ?? 'image/*')
            ->attribute('hidden', '');

        $dropzone->add($input);

        // ✅ Hidden inputs for existing file
        $hiddenInputs = $this->createHiddenInputs($form, $files, $field, false);
        $dropzone->add($hiddenInputs);

        return $dropzone;
    }

    /**
     * Render multiple dropzone with previews (original logic).
     */
    private function renderMultiplePopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent {
        $baseClass = $this->classConfig['multiple-class'];
        $previewClass = $baseClass . ' ' . $baseClass . '--preview';

        $dropzone = $form->tag('div')
            ->class($previewClass)
            ->custom([
                'data-state' => 'preview',
                'data-mode' => 'multiple',
            ]);

        // Previews grid
        $previewsGrid = $form->tag('div')->class($baseClass . '__previews-grid');

        foreach ($files as $index => $file) {
            $previewItem = $form->tag('div')
                ->class($baseClass . '__preview-item')
                ->custom(['data-index' => $index]);

            // Preview image or icon
            $preview = $form->tag('div')->class($baseClass . '__preview');
            $isImage = $file['is_image'] ?? false;
            if ($isImage) {
                $preview->add(
                    $form->tag('img')
                        ->src($file['web_path'] ?? '')
                        ->alt($field['alt'] ?? 'Preview'),
                );
            } else {
                $preview->add($this->createFileIcon($form, $file));
            }
            $previewItem->add($preview);

            // Actions (remove button)
            $actions = $form->tag('div')->class($baseClass . '__preview-item-actions');
            $actions->add(
                $form->button('button')
                    ->class('remove')
                    ->custom(['data-index' => $index])
                    ->content('×'),
            );
            $previewItem->add($actions);

            // File info
            $info = $form->tag('div')->class($baseClass . '__preview-info');
            $info->add(
                $form->tag('span')->class($baseClass . '__filename')
                    ->content($file['display_name'] ?? $file['original_name'] ?? 'File'),
                $form->tag('span')->class($baseClass . '__filesize')
                    ->content($file['formatted_size'] ?? $this->formatFileSize($file['size'] ?? 0)),
            );
            $previewItem->add($info);

            $previewsGrid->add($previewItem);
        }

        // Add "Add more" item
        $addMoreItem = $form->tag('div')
            ->class($baseClass . '__preview-item add-more-item');

        $addMorePreview = $form->tag('div')->class($baseClass . '__preview add-more');
        $addMorePreview->add(
            $form->tag('svg')->add(
                $form->tag('use')->attribute('href', '#icon-plus'),
            ),
        );
        $addMoreItem->add($addMorePreview);

        $addMoreInfo = $form->tag('div')->class($baseClass . '__preview-info');
        $addMoreInfo->add(
            $form->tag('span')->class($baseClass . '__filename')->content('Add more'),
        );
        $addMoreItem->add($addMoreInfo);

        $previewsGrid->add($addMoreItem);
        $dropzone->add($previewsGrid);

        // Content section (summary)
        $content = $form->tag('div')->class($baseClass . '__content');
        $info = $form->tag('div')->class($baseClass . '__info');

        $totalSize = array_sum(array_column($files, 'size'));
        $info->add(
            $form->tag('span')->class($baseClass . '__main-text')
                ->content(count($files) . ' file(s) uploaded successfully'),
            $form->tag('span')->class($baseClass . '__hint-text')
                ->content('Total: ' . $this->formatFileSize($totalSize)),
        );
        $content->add($info);

        $actions = $form->tag('div')->class($baseClass . '__actions');
        $actions->add(
            $form->button('button')
                ->class('add-more')
                ->content('Add More Files'),
        );
        $content->add($actions);
        $dropzone->add($content);

        // File input (hidden, multiple)
        $fieldName = $field['name'];
        if (!str_ends_with($fieldName, '[]')) {
            $fieldName = $fieldName . '[]';
        }

        $input = $form->input('file')
            ->id($fieldId)
            ->name($fieldName)
            ->accept($field['accept'] ?? 'image/*')
            ->attribute('hidden', '')
            ->attribute('multiple');

        $dropzone->add($input);

        // Hidden inputs for existing files
        $hiddenInputs = $this->createHiddenInputs($form, $files, $field, true);
        $dropzone->add($hiddenInputs);

        return $dropzone;
    }
}