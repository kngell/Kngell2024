<?php

declare(strict_types=1);

class FieldIdGenerator
{
    /** @var array<string, string> Cache of generated IDs by field signature */
    private array $idCache = [];

    private int $globalCounter = 0;

    public function generateId(string $formId, array $field): string
    {
        // Create a unique signature for this field
        $signature = $this->createSignature($formId, $field);

        // Return cached ID if already generated
        if (isset($this->idCache[$signature])) {
            return $this->idCache[$signature];
        }

        // If ID is explicitly provided, use it
        if (isset($field['id']) && !empty($field['id'])) {
            $this->idCache[$signature] = $field['id'];
            return $field['id'];
        }

        // Get the base identifier
        $identifier = $field['key'] ?? $field['name'] ?? 'field';
        $normalizedId = preg_replace('/[\[\]]/', '-', $identifier);
        $normalizedId = rtrim($normalizedId, '-');

        // Use global index if available, otherwise use counter
        if (isset($field['_globalIndex'])) {
            $id = $formId . '_' . $normalizedId . '_' . $field['_globalIndex'];
        } else {
            $this->globalCounter++;
            $id = $formId . '_' . $normalizedId . '_' . $this->globalCounter;
        }

        // Cache the ID
        $this->idCache[$signature] = $id;

        return $id;
    }

    public function reset(): void
    {
        $this->idCache = [];
        $this->globalCounter = 0;
    }

    private function createSignature(string $formId, array $field): string
    {
        return md5(implode('|', [
            $formId,
            $field['key'] ?? '',
            $field['name'] ?? '',
            $field['type'] ?? '',
            $field['_globalIndex'] ?? '',
            $field['_groupIndex'] ?? '',
            $field['_fieldIndex'] ?? '',
        ]));
    }
}