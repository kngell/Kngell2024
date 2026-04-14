<?php

declare(strict_types=1);

final class ModelOperationPayload
{
    private array $updatesById = [];
    private array $inserts = []; // NEW: Bucket for new entities
    private array $ids = [];
    private int|string|null $updateId = null;

    public function __construct(
        private mixed $data,
        private bool $isCollection,
        private string $entityClass,
        private ?string $keyProperty = null,
        private ?string $keyField = null,
    ) {
        $this->validate();
        $isCollection ? $this->getIds() : $this->getUpdateId();
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
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
            foreach ($this->data as $item) {
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

    /**
     * @return array
     */
    public function getUpdatesById(): array
    {
        return $this->updatesById;
    }

    public function getUpdateId(): int|string|null
    {
        if ($this->updateId !== null) {
            return $this->updateId;
        }
        $data = $this->data;
        $keyField = $this->keyField;
        if (is_array($data) && ArrayUtils::isAssoc($data)) {
            if (!isset($data[$keyField]) || empty($data[$keyField])) {
                $this->updateId = null;
            } else {
                $this->updateId = $data[$keyField] ?? null;
            }
        } elseif ($data instanceof Entity) {
            if ($data->entityKeyIsInitialzed()) {
                $this->updateId = $data->getEntityPrimarykeyValue();
            }
        } else {
            throw new DataAccessLayerException('Invalid update data type');
        }
        return  $this->updateId;
    }

    /**
     * @return null|string
     */
    public function getKeyField(): ?string
    {
        return $this->keyField;
    }

    private function resolveId(mixed $item): mixed
    {
        if (is_array($item)) {
            return (!empty($item[$this->keyField])) ? $item[$this->keyProperty] : null;
        }

        if ($item instanceof Entity) {
            return ($item->entityKeyIsInitialzed()) ? $item->getEntityPrimarykeyValue() : null;
        }

        return null;
    }

    private function validate(): void
    {
        if (!class_exists($this->entityClass)) {
            throw new InvalidArgumentException("Invalid entity class: {$this->entityClass}");
        }

        if ($this->isCollection && !ArrayUtils::isArrayList($this->data)) {
            throw new InvalidArgumentException('Collection data must be a sequential array');
        }

        if (!$this->isCollection && (is_array($this->data) && !ArrayUtils::isAssoc($this->data)) && !$this->data instanceof Entity) {
            throw new InvalidArgumentException('Single entity data must be an associative array or Entity');
        }
    }

    // Simple factory method
    public static function create(mixed $data, Entity $prototype): self
    {
        $isCollection = self::determineIfCollection($data);

        return new self(
            data: self::normalizeData($data, $isCollection),
            isCollection: $isCollection,
            entityClass: $prototype::class,
            keyProperty: $prototype->getEntityKeyProperty(),
            keyField: $prototype->getEntityKeyField(),
        );
    }

    private static function determineIfCollection(mixed $data): bool
    {
        if ($data instanceof CollectionInterface) {
            $data = $data->all();
        }
        if (is_array($data)) {
            return ArrayUtils::isArrayList($data) || ArrayUtils::isObjectList($data);
        }

        return false;
    }

    private static function normalizeData(mixed $data, bool $isCollection): mixed
    {
        if ($data instanceof Entity && !$isCollection) {
            return $data;
        }

        if ($data instanceof CollectionInterface) {
            $items = [];
            foreach ($data as $item) {
                if ($item instanceof Entity || (is_array($item) && ArrayUtils::isAssoc($item))) {
                    $items[] = $item;
                } else {
                    throw new InvalidArgumentException('Collection data must be a sequential array or an Entity');
                }
            }
            return $items;
        }

        if (is_array($data)) {
            if ($isCollection) {
                $items = [];
                foreach ($data as $item) {
                    if ($item instanceof Entity || (is_array($item) && ArrayUtils::isAssoc($item))) {
                        $items[] = $item;
                    } else {
                        throw new InvalidArgumentException('Collection data must be a sequential array or an Entity');
                    }
                }
                return $items;
            }
            return $data;
        }

        throw new InvalidArgumentException('Invalid data type');
    }
}
