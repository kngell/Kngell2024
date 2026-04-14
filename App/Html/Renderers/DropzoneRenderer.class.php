<?php

declare(strict_types=1);

class DropzoneRenderer
{
    /** @var array<string, DropzoneHandlerInterface> */
    private array $handlers = [];

    public function __construct(
        private readonly FileMetadataService $fileMetadataService,
        private readonly IconBuilder $iconBuilder,
        array $handlerConfig = [],
    ) {
        $this->registerDefaultHandlers($handlerConfig);
    }

    public function registerHandler(DropzoneHandlerInterface $handler): void
    {
        $this->handlers[$handler->getName()] = $handler;
    }

    public function render(array $field, FormBuilder $form, AbstractForm $formInstance, int $globalIndex): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);

        // Resolve which handler to use
        $handler = $this->resolveHandler($field, $formInstance);

        // Get files if any
        $files = $this->getProcessedFiles($field, $formInstance);

        // Let the handler render appropriate state
        if (empty($files)) {
            return $handler->renderEmpty($field, $form, $formInstance, $fieldId);
        }

        return $handler->renderPopulated($field, $form, $files, $fieldId);
    }

    private function registerDefaultHandlers(array $handlerConfig): void
    {
        // Modern handler with optional config
        $modernConfig = $handlerConfig['modern'] ?? [];
        $this->handlers['modern'] = new ModernDropzoneHandler(
            $this->iconBuilder,
            $this->fileMetadataService,
            $modernConfig,
        );

        // Classic handler
        $this->handlers['classic'] = new ClassicDropzoneHandler(
            $this->iconBuilder,
            $this->fileMetadataService,
        );
    }

    private function resolveHandler(array $field, AbstractForm $formInstance): DropzoneHandlerInterface
    {
        // Priority 1: Field explicitly specifies handler
        if (isset($field['dropzoneHandler']) && isset($this->handlers[$field['dropzoneHandler']])) {
            return $this->handlers[$field['dropzoneHandler']];
        }

        // Priority 2: Field specifies style
        if (isset($field['dropzoneStyle']) && isset($this->handlers[$field['dropzoneStyle']])) {
            return $this->handlers[$field['dropzoneStyle']];
        }

        // Priority 3: Map from input layout
        $layout = $formInstance->getDefaultInputLayoutName();
        if ($layout === 'modern' || $layout === 'input-field') {
            return $this->handlers['modern'];
        }

        // Default to classic
        return $this->handlers['classic'];
    }

    private function getProcessedFiles(array $field, AbstractForm $formInstance): array
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

            if ($file instanceof FileInformation) {
                $metadata = $this->fileMetadataService->getUploadMetadata($file, $fieldName);
                $processed[] = $metadata;
            } elseif (is_string($file) && file_exists($file)) {
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
}