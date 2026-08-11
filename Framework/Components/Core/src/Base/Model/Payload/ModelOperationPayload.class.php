<?php

declare(strict_types=1);

final class ModelOperationPayload
{
    private array $updatesById = [];
    private array $inserts = [];
    private array $ids = [];
    private int|string|null $updateId = null;
    private mixed $normalizedData = null;

    public function __construct(
        private mixed $data,
        private bool $isCollection,
        private Entity $prototype,
        private ?string $keyProperty = null,
        private ?string $keyField = null,
    ) {
        $this->normalizedData = $this->normalizeDataWithTypeHandler($data);
        $this->validate();
        $isCollection ? $this->getIds() : $this->getUpdateId();
    }

    public function getData(): mixed
    {
        return $this->normalizedData;
    }

    public function getRawData(): mixed
    {
        return $this->data;
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function getEntityClass(): string
    {
        return $this->prototype::class;
    }

    public function getPrototype(): Entity
    {
        return $this->prototype;
    }

    public function getKeyProperty(): ?string
    {
        return $this->keyProperty;
    }

    public function getIds(): array
    {
        if (!$this->keyProperty) {
            return [];
        }
        if (!empty($this->ids) || !empty($this->inserts)) {
            return $this->ids;
        }

        if ($this->isCollection()) {
            foreach ($this->normalizedData as $item) {
                $id = $this->resolveId($item);
                if ($id !== null) {
                    $this->ids[] = $id;
                    $this->updatesById[$id] = $item;
                } else {
                    $this->inserts[] = $item;
                }
            }
        }
        return $this->ids;
    }

    public function getInserts(): array
    {
        return $this->inserts;
    }

    public function hasInserts(): bool
    {
        return !empty($this->inserts);
    }

    public function hasIds(): bool
    {
        return !empty($this->getIds());
    }

    public function hasId(): bool
    {
        return !empty($this->updateId) || !empty($this->ids);
    }

    public function isConditionalOnly(): bool
    {
        return !$this->hasId() && !$this->hasIds();
    }

    public function getUpdatesById(): array
    {
        return $this->updatesById;
    }

    public function getUpdateId(): int|string|null
    {
        if ($this->updateId !== null) {
            return $this->updateId;
        }

        $data = $this->normalizedData;
        $keyField = $this->keyField;

        if (is_array($data) && ArrayUtils::isAssoc($data)) {
            // Data is already normalized, ID is raw
            $this->updateId = $data[$keyField] ?? $data['id'] ?? null;
        } elseif ($data instanceof Entity) {
            if ($data->entityKeyIsInitialzed()) {
                $this->updateId = $data->getEntityPrimarykeyValue();
            }
        } else {
            $this->updateId = null;
        }

        return $this->updateId;
    }

    public function getKeyField(): ?string
    {
        return $this->keyField;
    }

    private function normalizeDataWithTypeHandler(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        // Single entity
        if (!$this->isCollection) {
            return $this->normalizeSingleItem($data);
        }

        // Collection
        if ($data instanceof CollectionInterface) {
            $data = $data->all();
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Collection data must be an array');
        }

        $normalized = [];
        foreach ($data as $item) {
            $normalized[] = $this->normalizeSingleItem($item);
        }
        return $normalized;
    }

    private function normalizeSingleItem(mixed $item): mixed
    {
        // Already an entity - return as-is
        if ($item instanceof Entity) {
            return $item;
        }

        // Must be an array to normalize
        if (!is_array($item)) {
            throw new InvalidArgumentException('Data must be an array or Entity');
        }

        $normalizedEntity = clone $this->prototype;

        $normalizedEntity->assign($item);

        return $normalizedEntity;
    }

    private function resolveId(mixed $item): mixed
    {
        if ($item instanceof Entity) {
            return $item->entityKeyIsInitialzed() ? $item->getEntityPrimarykeyValue() : null;
        }

        if (is_array($item) && isset($item[$this->keyField])) {
            return $item[$this->keyField];
        }

        return null;
    }

    private function validate(): void
    {
        if ($this->isCollection && !is_array($this->normalizedData)) {
            throw new InvalidArgumentException('Collection data must be an array after normalization');
        }

        if (!$this->isCollection && !$this->normalizedData instanceof Entity && !is_array($this->normalizedData)) {
            throw new InvalidArgumentException('Single entity data must be an Entity or array after normalization');
        }
    }

    // Factory method - THE ONLY WAY to create a payload
    public static function create(mixed $data, Entity $prototype): self
    {
        $isCollection = self::determineIfCollection($data);

        return new self(
            data: $data,  // Pass raw data - normalization happens in constructor
            isCollection: $isCollection,
            prototype: $prototype,
            keyProperty: $prototype->getEntityKeyProperty(),
            keyField: $prototype->getEntityKeyField(),
        );
    }

    private static function determineIfCollection(mixed $data): bool
    {
        if ($data instanceof CollectionInterface) {
            return true;
        }
        if (is_array($data)) {
            return ArrayUtils::isArrayList($data) || ArrayUtils::isObjectList($data);
        }
        return false;
    }
}