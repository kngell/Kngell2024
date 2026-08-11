<?php

declare(strict_types=1);

final class FileExtractor
{
    private array $files = [];

    public function __construct(
        private FileMetadataService $metadataService,
        private array $formValues,
        private array $mediaNames,
        private bool $isEditMode,
    ) {
    }

    public function getFiles(array $field): array
    {
        $fieldName = $field['name'];
        if ($this->isEditMode && !empty($this->formValues)) {
            $mediaData = [];
            $values = $this->formValues instanceof Entity
                ? $this->formValues->toArray()
                : $this->formValues;

            foreach ($this->mediaNames as $name) {
                if (substr($name, -2) === '[]') {
                    $basePattern = substr($name, 0, -2);
                    $indexedValues = [];
                    $i = 0;

                    while (isset($values[$basePattern . '[' . $i . ']'])) {
                        $indexedValues[] = $values[$basePattern . '[' . $i . ']'];
                        unset($this->formValues[$basePattern . '[' . $i . ']']);
                        $i++;
                    }

                    if (!empty($indexedValues)) {
                        $mediaData[$name] = $indexedValues;
                    }
                } else {
                    // Regular field without []
                    if (isset($values[$name])) {
                        $mediaData[$name] = $values[$name];
                        unset($this->formValues[$name]);
                    }
                }
            }

            if (!empty($mediaData)) {
                $this->files = $this->metadataService->createFromWebPaths($mediaData);
            }
        }
        if ($fieldName !== null && !empty($this->files)) {
            if (substr($fieldName, -2) === '[]') {
                $baseName = substr($fieldName, 0, -2);
                if (isset($this->files[$baseName])) {
                    return $this->files[$baseName];
                }
            }

            // Try exact match
            if (isset($this->files[$fieldName])) {
                return $this->files[$fieldName];
            }

            // Try without trailing []
            $withoutBrackets = rtrim($fieldName, '[]');
            if (isset($this->files[$withoutBrackets])) {
                return $this->files[$withoutBrackets];
            }
        }

        return $this->files ?? [];
    }
}