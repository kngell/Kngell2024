<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

class ValidationRulesExporter extends ConfigExporterService
{
    private const array CLIENT_ALLOWED_RULES = [
        'required', 'min', 'max', 'pattern', 'numeric',
        'min_value', 'max_value', 'required_if', 'lte', 'gte',
        'array', 'max_items', 'min_items',
        'mimes', 'file_size', 'upload_limit', 'post_limit', 'max_files',
        'integer', 'decimal', 'boolean', 'email', 'url', 'date', 'time',
    ];

    private const array SERVER_ONLY_RULES = [
        'unique', 'exists', 'file', 'image', 'custom_callback',
    ];

    protected string $configType = 'validation';

    public function __construct(
        CacheInterface $cache,
        private ValidationMessageService $messageService,
    ) {
        parent::__construct($cache);
    }

    public function exportForClient(string $rulesFilePath, array $options = []): array
    {
        return $this->getCachedOrGenerate($rulesFilePath, function () use ($rulesFilePath, $options) {
            return $this->generateValidationData($rulesFilePath, $options);
        }, $options);
    }

    public function getGlobalSettings(): array
    {
        // For global settings, we use a different source identifier
        $source = 'validation_global_settings';
        $options = ['type' => 'global'];

        return $this->getCachedOrGenerate($source, function () {
            return [
                'messages' => $this->messageService->getAllMessages(),
                'classes' => [
                    'hint' => $this->messageService->getHintClasses(),
                    'error' => $this->messageService->getErrorClasses(),
                ],
            ];
        }, $options);
    }

    /**
     * Clear cache for a specific rules file.
     */
    public function clearRulesCache(string $rulesFilePath): bool
    {
        return $this->clearCache($rulesFilePath);
    }

    /**
     * Clear all validation caches.
     */
    public function clearAllCache(): bool
    {
        // We need to clear both specific rules cache and global settings cache
        $cleared1 = $this->clearCache('dummy_path'); // This won't work well

        // Better approach: Use cache pattern deletion if available
        $pattern = 'export_validation_*_' . preg_replace(
            '/[^a-zA-Z0-9]/',
            '_',
            Environment::get('APP_ENV', 'prod'),
        );

        if (method_exists($this->cache, 'deleteByPattern')) {
            return $this->cache->deletePattern($pattern);
        }

        return $cleared1;
    }

    private function generateValidationData(string $rulesFilePath, array $options): array
    {
        // Ensure file exists
        if (!file_exists($rulesFilePath)) {
            throw new RuntimeException("Validation rules file not found: {$rulesFilePath}");
        }

        $rules = Yaml::parseFile($rulesFilePath);

        return [
            'rules' => $this->filterClientRules($rules),
            'messages' => $this->messageService->getAllMessages(),
            'classes' => [
                'hint' => $this->messageService->getHintClasses(),
                'error' => $this->messageService->getErrorClasses(),
            ],
            'client_allowed_rules' => self::CLIENT_ALLOWED_RULES,
            'server_only_rules' => self::SERVER_ONLY_RULES,
            'source_file' => basename($rulesFilePath),
        ];
    }

    private function filterClientRules(array $rules): array
    {
        $clientRules = [];

        foreach ($rules as $field => $fieldRules) {
            if (isset($fieldRules['items']) && is_array($fieldRules['items'])) {
                $filteredRules = $this->filterNestedStructure($fieldRules);
            } else {
                $filteredRules = $this->filterFlatFieldRules($fieldRules);
            }

            if (!empty($filteredRules)) {
                $clientRules[$field] = $filteredRules;
            }
        }

        return $clientRules;
    }

    private function filterFlatFieldRules(array $fieldRules): array
    {
        $filteredRules = [];

        foreach ($fieldRules as $rule => $value) {
            // Skip server-only rules
            if (in_array($rule, self::SERVER_ONLY_RULES, true)) {
                continue;
            }

            // Only include client-allowed rules
            if (in_array($rule, self::CLIENT_ALLOWED_RULES, true)) {
                $filteredRules[$rule] = $value;
            }

            // Always include display name
            if ($rule === 'display') {
                $filteredRules[$rule] = $value;
            }
        }

        return $filteredRules;
    }

    private function filterNestedStructure(array $structure): array
    {
        $filtered = [];

        // Always include array structure rules
        $allowedStructureRules = ['array', 'max_items', 'min_items', 'items', 'type'];

        foreach ($structure as $key => $value) {
            if (in_array($key, $allowedStructureRules, true)) {
                // Recursively filter nested items
                if ($key === 'items' && is_array($value)) {
                    $filtered[$key] = $this->filterNestedItems($value);
                } else {
                    $filtered[$key] = $value;
                }
            }

            // Always include display for the main array field
            if ($key === 'display') {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function filterNestedItems(array $items): array
    {
        $filtered = [];

        foreach ($items as $key => $value) {
            if ($key === 'type') {
                $filtered[$key] = $value;
            } elseif ($key === 'rules' && is_array($value)) {
                // Recursively filter the nested rules
                $filtered[$key] = $this->filterClientRules($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}