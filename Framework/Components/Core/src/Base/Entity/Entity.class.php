<?php

declare(strict_types=1);

abstract class Entity
{
    protected const array RELATIONSHIPS = [];

    private array $tableAlias;
    private array $tableMap;
    private array $relatedEntities = [];
    private array $pendingData = [];
    private array $pendingCollections = [];
    private ?array $cachedFieldMap = null;

    public function __construct(
        private EntityDependenciesFactoryInterface $dependencies,
        array $tableAlias = [],
        array $tableMap = [],
    ) {
        $this->tableAlias = $tableAlias;
        $this->tableMap = $tableMap;
    }

    // --------------------------------------------------------
    // MAGIC METHODS & HYDRATION (using the protected getters)
    // --------------------------------------------------------
    public function __set(string $name, mixed $value): void
    {
        $isRelation = isset(static::RELATIONSHIPS[$name]);

        if ($isRelation && is_array($value) && !ArrayUtils::isArrayList($value)) {
            $this->getRelationManager()->hydrateRelatedEntity(
                entity: $this,
                dbRelationName: $name,
                field: '_all_data',
                value: $value,
                tableAlias: $this->tableAlias,
                tableMap: $this->tableMap,
                relatedEntities: $this->relatedEntities,
            );
            return;
        }

        $name = $this->getRelationManager()->resolveRealName(
            $this,
            $name,
            $this->tableAlias,
            $this->tableMap,
            static::RELATIONSHIPS,
        );

        if (str_contains($name, '.')) {
            $parts = explode('.', $name);

            $currentPath = '';
            foreach ($parts as $i => $part) {
                $currentPath = $currentPath ? $currentPath . '.' . $part : $part;

                if (isset(static::RELATIONSHIPS[$currentPath])) {
                    $remainingPath = implode('.', array_slice($parts, $i + 1));

                    $this->getRelationManager()->hydrateRelatedEntity(
                        entity: $this,
                        dbRelationName: $currentPath,
                        field: $remainingPath,
                        value: $value,
                        tableAlias: $this->tableAlias,
                        tableMap: $this->tableMap,
                        relatedEntities: $this->relatedEntities,
                    );
                    return;
                }
            }
        }

        if ($this->getRelationManager()->hasActiveRelationships($this, $this->tableAlias, $this->relatedEntities)) {
            $this->pendingData[$name] = $value;
        } else {
            $this->getHydrator()->denormalizeAndSetProperty($this, $name, $value);
        }
    }

    public function debugPendingCollections(): array
    {
        return [
            'pendingCollections' => $this->pendingCollections,
            'relatedEntities' => $this->relatedEntities,
            'relationships' => static::RELATIONSHIPS,
        ];
    }

    public function assign(array $data): self
    {
        return $this->getHydrator()->assign($this, $data);
    }

    public function pdoHydrate(array $data): void
    {
        $this->getHydrator()->pdoHydrate($this, $data);
    }

    public function completeHydration(): self
    {
        if (!empty($this->pendingData) || !empty($this->relatedEntities)) {
            $this->getHydrator()->completeMainHydration($this, $this->pendingData, $this->cachedFieldMap);
            $this->getRelationManager()->completeRelatedEntityHydration($this, $this->relatedEntities);
            $this->relatedEntities = [];
        }

        return $this;
    }

    // -----------------------------------------------------------
    // TRANSFORMATION METHODS
    // -----------------------------------------------------------

    public function toOriginalArray(): array
    {
        return $this->getTransformer()->toOriginalArray($this);
    }

    public function toArray(): array
    {
        return $this->getTransformer()->toArray($this);
    }

    public function toDeepArray(
        bool $includeRelationships = true,
        int $maxDepth = 2,
        array $excludedProperties = [],
    ): array {
        return $this->getTransformer()->toDeepArray(
            $this,
            $includeRelationships,
            $maxDepth,
            $excludedProperties,
        );
    }

    public function toFlattenedArray(
        string $separator = '.',
        bool $includeRelationships = true,
        array $excludedProperties = [],
    ): array {
        return $this->getTransformer()->toFlattenedArray(
            $this,
            $separator,
            $includeRelationships,
            $excludedProperties,
        );
    }

    public function toFormArray(
        array $fieldMapping = [],
        bool $flattenNested = true,
        bool $formatValues = true,
    ): array {
        return $this->getTransformer()->toFormArray(
            $this,
            $fieldMapping,
            $flattenNested,
            $formatValues,
        );
    }

    public function toDatabaseArray(bool $includeRelationships = false): array
    {
        return $this->getTransformer()->toDatabaseArray($this, $includeRelationships);
    }

    public function extractRelationshipIds(): array
    {
        return $this->getTransformer()->extractRelationshipIds($this, static::RELATIONSHIPS);
    }

    // ----------------------------------------------------------
    // DATABASE & MAPPING METHODS
    // ----------------------------------------------------------

    public function table(): string
    {
        return $this->getMapper()->getTableName($this);
    }

    public function getRelationClassName(string $relationBdName): ?string
    {
        $relationships = $this->getRelationships();
        return $relationships[$relationBdName] ?? null;
    }

    public function getRelationPropertyName(string $officialKey): string
    {
        $camel = StringUtils::camelCase($officialKey);
        if (property_exists($this, $camel)) {
            return $camel;
        }
        if (property_exists($this, $camel . 'Show')) {
            return $camel . 'Show';
        }
        return $camel;
    }

    public function getRelationshipKeyFromDataKey(string $dataKey): ?string
    {
        $relationships = $this->getRelationships();
        if (isset($relationships[$dataKey])) {
            return $dataKey;
        }
        $cleanKey = str_replace('_show', '', $dataKey);
        if (isset($relationships[$cleanKey])) {
            return $cleanKey;
        }

        return null;
    }

    public function getCurrencyCodeIfExists(): ?string
    {
        return $this->getMapper()->getCurrencyCodeIfExists($this);
    }

    public function convertToPropertyName(string $fieldName): string
    {
        return $this->getMapper()->convertToPropertyName($fieldName);
    }

    public function getCurrencyIdIfExists(): int|string|null
    {
        return $this->getMapper()->getCurrencyIdIfExists($this);
    }

    // In Entity class:
    public function getAllProperties(): array
    {
        return $this->getMapper()->getAllProperties($this);
    }

    public function getEntityKeyField(): string|bool
    {
        return $this->getMapper()->getEntityKeyField($this);
    }

    public function getEntityKeyProperty(): string|bool
    {
        return $this->getMapper()->getEntityKeyProperty($this);
    }

    public function getProperty(string $field): ?ReflectionProperty
    {
        return $this->getMapper()->getPropertyForField($this, $field);
    }

    public function hasProperty(string $propertyName): bool
    {
        return $this->getMapper()->hasProperty($this, $propertyName);
    }

    public function isInitialized(string $field): bool
    {
        return $this->getMapper()->isInitialized($this, $field);
    }

    public function getFieldValue(string $field): mixed
    {
        return $this->getMapper()->getFieldValue($this, $field);
    }

    // ------------------------------------------------------------
    // OTHER METHODS
    // ------------------------------------------------------------

    public function getDirtyData(): array
    {
        return $this->getHydrator()->getDirtyData($this);
    }

    public function isEmpty(): bool
    {
        $data = $this->toArray();

        foreach ($data as $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    public function hasRelationships(): bool
    {
        $relationships = static::RELATIONSHIPS ?? [];
        return !empty($relationships);
    }

    public function getRelationshipsName(string $relationName): string
    {
        return static::RELATIONSHIPS[$relationName];
    }

    public function getRelationships(): array
    {
        return static::RELATIONSHIPS;
    }

    public function hydrateWithRelations(): void
    {
        $this->completeHydration();
    }

    /**
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * @return array
     */
    public function getTableMap(): array
    {
        return $this->tableMap;
    }

    /**
     * @return array
     */
    public function &getRelatedEntities(): array
    {
        return $this->relatedEntities;
    }

    public function getRelationManager(): EntityRelationManagerInterface
    {
        return $this->dependencies->getRelationManager();
    }

    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->dependencies->getChangeTracker();
    }

    // Protected getters for internal use
    protected function getMapper(): EntityMapperInterface
    {
        return $this->dependencies->getMapper();
    }

    protected function getHydrator(): EntityHydratorInterface
    {
        return $this->dependencies->getHydrator();
    }

    protected function getTransformer(): EntityToArrayTransformerInterface
    {
        return $this->dependencies->getTransformer();
    }

    protected function getNormalizer(): TypeNormalizerInterface
    {
        return $this->dependencies->getNormalizer();
    }

    // protected function getTypeHandlerFactory(): TypeHandlerFactory
    // {
    //     return $this->dependencies->getTypeHandlerFactory();
    // }

    protected function getTypePresenterFactory(): TypePresenterFactory
    {
        return $this->dependencies->getTypePresenterFactory();
    }
}