<?php

declare(strict_types=1);

class FieldIdGenerator
{
    /** @var array<string, string> Cache of generated IDs by field signature */
    private array $idCache = [];

    public function generateId(string $formId, array $field): string
    {
        // Create a unique signature for this field
        $fieldSignature = $this->createFieldSignature($formId, $field);

        // Return cached ID if already generated for this field
        if (isset($this->idCache[$fieldSignature])) {
            return $this->idCache[$fieldSignature];
        }

        // If ID is explicitly provided, use it as-is
        if (isset($field['id']) && !empty($field['id'])) {
            $this->idCache[$fieldSignature] = $field['id'];
            return $field['id'];
        }

        $identifier = $field['key'] ?? $field['name'] ?? 'field';

        // Simple normalization
        $normalizedId = preg_replace('/[\[\]]/', '-', $identifier);
        $normalizedId = rtrim($normalizedId, '-');
        $baseId = $formId . '-' . $normalizedId;

        // Use global index for guaranteed uniqueness
        $finalId = $this->ensureUniqueId($baseId, $field);
        $this->idCache[$fieldSignature] = $finalId;

        return $finalId;
    }

    private function createFieldSignature(string $formId, array $field): string
    {
        $parts = [
            $formId,
            $field['key'] ?? '',
            $field['name'] ?? '',
            $field['type'] ?? 'text',
            $field['id'] ?? '',
            $field['_groupIndex'] ?? '',
            $field['_fieldIndex'] ?? '',
            $field['_globalIndex'] ?? '', // Add global index
            $field['_wrapperClass'] ?? '',
            // Add section context if available
            $field['_sectionKey'] ?? '',
        ];

        return md5(implode('|', array_filter($parts)));
    }

    private function ensureUniqueId(string $baseId, array $field): string
    {
        // If we have a global index, use it for guaranteed uniqueness
        if (isset($field['_globalIndex'])) {
            return $baseId . '-' . $field['_globalIndex'];
        }

        // Fallback: use static counter
        static $fallbackCounters = [];

        if (!isset($fallbackCounters[$baseId])) {
            $fallbackCounters[$baseId] = 1;
            return $baseId;
        }

        $fallbackCounters[$baseId]++;
        return $baseId . '-' . $fallbackCounters[$baseId];
    }

    public function reset(): void
    {
        $this->idCache = [];
    }
}