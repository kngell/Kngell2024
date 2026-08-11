<?php

declare(strict_types=1);

class FieldIdGenerator
{
    /** @var array<string, string> Cache of generated IDs by field signature */
    private array $idCache = [];

    /** @var array<string, int> Tracker for duplicate IDs */
    private array $idTracker = [];

    private int $globalCounter = 0;

    public function generateId(string $formId, array $field = []): string
    {
        $signature = $this->createSignature($formId, $field);

        if (isset($this->idCache[$signature])) {
            return $this->idCache[$signature];
        }

        if (isset($field['id']) && !empty($field['id'])) {
            $uniqueId = $this->getUniqueId($field['id']);
            $this->idCache[$signature] = $uniqueId;
            return $uniqueId;
        }

        $identifier = $field['key'] ?? $field['name'] ?? 'field';
        $normalizedId = $this->normalizeIdentifier($identifier);

        if (isset($field['_globalIndex'])) {
            $baseId = $formId . '_' . $normalizedId . '_' . $field['_globalIndex'];
        } else {
            $this->globalCounter++;
            $baseId = $formId . '_' . $normalizedId . '_' . $this->globalCounter;
        }

        $uniqueId = $this->getUniqueId($baseId);
        $this->idCache[$signature] = $uniqueId;

        return $uniqueId;
    }

    public function getUniqueId(?string $id = null): string
    {
        if ($id === null) {
            $id = 'fieldId';
        }
        if (!isset($this->idTracker[$id])) {
            $this->idTracker[$id] = 1;
            return $id;
        }

        $counter = $this->idTracker[$id];
        $uniqueId = $id . '_' . $counter;
        $this->idTracker[$id]++;
        $this->idTracker[$uniqueId] = 1;

        return $uniqueId;
    }

    public function reset(): void
    {
        error_log('[FieldIdGenerator] RESET called!');
        $this->idCache = [];
        $this->idTracker = [];
        $this->globalCounter = 0;
    }

    public function getTrackedIds(): array
    {
        return array_keys($this->idTracker);
    }

    public function isIdUsed(string $id): bool
    {
        return isset($this->idTracker[$id]);
    }

    private function normalizeIdentifier(string $identifier): string
    {
        $normalized = preg_replace('/[\[\]]/', '-', $identifier);
        return rtrim($normalized, '-');
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