<?php

declare(strict_types=1);

final class ModelQueryPayload
{
    private array $normalizedConditions = [];
    private array $originalConditions = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $deleteOption = null;
    private ?DateTimeImmutable $archivedAt = null;
    private array $with = [];
    private array $select = [];

    public function __construct(
        private Entity $prototype,
        mixed $conditions,
    ) {
        $this->originalConditions = is_array($conditions) ? $conditions : [$conditions];
        $this->normalizeConditions();
    }

    public function getConditions(): array
    {
        return $this->normalizedConditions;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getDeleteOption(): ?string
    {
        return $this->deleteOption;
    }

    public function getArchivedAt(): ?DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function getWith(): array
    {
        return $this->with;
    }

    public function getSelect(): array
    {
        return $this->select;
    }

    public function hasConditions(): bool
    {
        return !empty($this->normalizedConditions);
    }

    private function normalizeConditions(): void
    {
        foreach ($this->originalConditions as $key => $value) {
            if (is_int($key)) {
                $this->normalizedConditions[$key] = $value;
                continue;
            }

            // Handle special query modifiers (only for string keys)
            if ($this->isQueryModifier($key)) {
                $this->handleQueryModifier($key, $value);
                continue;
            }

            // Handle nested conditions (AND/OR groups)
            if (is_array($value) && $this->isConditionGroup($key)) {
                $this->normalizedConditions[$key] = $this->normalizeConditionGroup($value);
                continue;
            }

            // Handle regular field conditions
            $this->normalizedConditions[$key] = $this->normalizeValue($value, $key);
        }
    }

    private function isQueryModifier(string $key): bool
    {
        return in_array(strtoupper($key), [
            'ORDER_BY', 'ORDER', 'SORT',
            'LIMIT', 'OFFSET',
            'DELETE_OPTION', 'SOFT_DELETE',
            'WITH', 'EAGER',
            'SELECT', 'COLUMNS',
            'ARCHIVED_AT',
        ]);
    }

    private function handleQueryModifier(string $key, mixed $value): void
    {
        switch (strtoupper($key)) {
            case 'ORDER_BY':
            case 'ORDER':
            case 'SORT':
                $this->orderBy = $this->normalizeOrderBy($value);
                break;
            case 'LIMIT':
                $this->limit = (int) $value;
                break;
            case 'OFFSET':
                $this->offset = (int) $value;
                break;
            case 'DELETE_OPTION':
            case 'SOFT_DELETE':
                $this->deleteOption = (string) $value;
                break;
            case 'ARCHIVED_AT':
                $this->archivedAt = $this->normalizeDateTime($value);
                break;
            case 'WITH':
            case 'EAGER':
                $this->with = is_array($value) ? $value : [$value];
                break;
            case 'SELECT':
            case 'COLUMNS':
                $this->select = is_array($value) ? $value : [$value];
                break;
        }
    }

    private function isConditionGroup(string $key): bool
    {
        return in_array(strtoupper($key), ['AND', 'OR', 'NOT']);
    }

    private function normalizeConditionGroup(array $group): array
    {
        $normalized = [];
        foreach ($group as $subKey => $subValue) {
            // Skip numeric keys in condition groups - pass through as-is
            if (is_int($subKey)) {
                $normalized[$subKey] = $subValue;
                continue;
            }

            if ($this->isConditionGroup($subKey)) {
                $normalized[$subKey] = $this->normalizeConditionGroup($subValue);
            } else {
                $normalized[$subKey] = $this->normalizeValue($subValue, $subKey);
            }
        }
        return $normalized;
    }

    private function normalizeValue(mixed $value, ?string $fieldName = null): mixed
    {
        // Handle array of values (IN clause)
        if (is_array($value) && !$this->isConditionGroup($fieldName ?? '')) {
            return array_map(fn ($item) => $this->normalizeSingleValue($item, $fieldName), $value);
        }

        return $this->normalizeSingleValue($value, $fieldName);
    }

    private function normalizeSingleValue(mixed $value, ?string $fieldName = null): mixed
    {
        if ($fieldName !== null && $this->isKeyField($fieldName)) {
            return $this->deobfuscateIfNeeded($value);
        }

        if (is_string($value) && ObfuscationUtils::isObfuscated($value)) {
            return $this->deobfuscateForeignValue($value, $fieldName);
        }

        if (is_string($value) && $this->isDateTimeString($value)) {
            return $this->normalizeDateTime($value);
        }

        return $value;
    }

    private function isKeyField(string $fieldName): bool
    {
        $keyField = $this->prototype->getEntityKeyField();
        return $fieldName === $keyField;
    }

    private function isObfuscationEnabled(): bool
    {
        $format = $this->prototype->getFormat();
        return $format?->obfuscate === true;
    }

    private function deobfuscateIfNeeded(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        if (!$this->isObfuscationEnabled()) {
            return $value;
        }

        if (!ObfuscationUtils::isObfuscated($value)) {
            return $value;
        }

        $keyProperty = $this->prototype->getEntityKeyProperty();
        $property = $this->prototype->getProperty($keyProperty);
        $normalizer = $this->prototype->getNormalizer();

        return $normalizer->normalizeForClientToEntity($value, $property, $this->prototype);
    }

    private function deobfuscateForeignValue(string $value, ?string $fieldName = null): mixed
    {
        $property = $this->prototype->getProperty($fieldName);
        $normalizer = $this->prototype->getNormalizer();

        $rawId = $normalizer->normalizeForClientToEntity($value, $property, $this->prototype);

        if ($rawId === null) {
            return $value;
        }

        return is_numeric($rawId) ? (int) $rawId : $rawId;
    }

    private function isDateTimeString(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $value) === 1;
    }

    private function normalizeDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (is_string($value) && !empty($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function normalizeOrderBy(mixed $value): array
    {
        if (is_string($value)) {
            $parts = explode(' ', trim($value));
            $field = $parts[0];
            $direction = strtoupper($parts[1] ?? 'ASC');
            return [$field => $direction === 'DESC' ? 'DESC' : 'ASC'];
        }

        if (is_array($value)) {
            $orderBy = [];
            foreach ($value as $field => $direction) {
                if (is_int($field)) {
                    $parts = explode(' ', trim($direction));
                    $field = $parts[0];
                    $dir = strtoupper($parts[1] ?? 'ASC');
                    $orderBy[$field] = $dir === 'DESC' ? 'DESC' : 'ASC';
                } else {
                    $orderBy[$field] = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                }
            }
            return $orderBy;
        }

        return [];
    }

    public static function create(Entity $prototype, mixed $conditions): self
    {
        return new self($prototype, $conditions);
    }
}