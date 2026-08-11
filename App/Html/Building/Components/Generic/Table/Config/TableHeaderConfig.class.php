<?php

declare(strict_types=1);

class TableHeaderConfig
{
    public function __construct(
        private readonly FileContentManager $fileManager,
    ) {
    }

    /**
     * @return TableColumnConfig[]
     */
    public function load(string $configPath): array
    {
        $extension = pathinfo($configPath, PATHINFO_EXTENSION);

        $rawColumns = match ($extension) {
            'php' => $this->loadFromPhp($configPath),
            'json' => $this->loadFromJson($configPath),
            default => throw new FileException(
                "Unsupported config format: .{$extension}. Use .php or .json",
            ),
        };

        $this->validateRawColumns($rawColumns, $configPath);

        return TableColumnConfig::collection($rawColumns);
    }

    private function loadFromPhp(string $configPath): array
    {
        $result = $this->fileManager->requirePhp($configPath);

        if (!is_array($result)) {
            throw new FileException(
                "PHP config must return an array: {$configPath}",
            );
        }

        return $result;
    }

    private function loadFromJson(string $configPath): array
    {
        $jsonFile = new JsonFile($configPath, $this->fileManager);
        return $jsonFile->getContentAsArray();
    }

    /**
     * @param mixed  $columns
     * @param string $path
     */
    private function validateRawColumns(mixed $columns, string $path): void
    {
        if (!is_array($columns)) {
            throw new FileException(
                "Config must resolve to an array: {$path}",
            );
        }

        if (empty($columns)) {
            throw new FileException(
                "Config must contain at least one column: {$path}",
            );
        }

        // Validate each column has at minimum a 'key' and 'type'
        foreach ($columns as $index => $column) {
            if (!is_array($column)) {
                throw new FileException(
                    "Column at index {$index} must be an array in: {$path}",
                );
            }
            if (empty($column['key'])) {
                throw new FileException(
                    "Column at index {$index} missing 'key' in: {$path}",
                );
            }
        }
    }
}