<?php

declare(strict_types=1);

abstract class Entity
{
    protected const array RELATIONSHIPS = [];

    private bool $_isTracking = false;
    private array $tableAlias;
    private array $tableMap;
    private array $relatedEntities = [];
    private array $pendingData = [];
    private ?array $cachedFieldMap = null;

    public function __construct(
        private EntityDependenciesFactoryInterface $dependencies,
        array $tableAlias = [],
        array $tableMap = [],
    ) {
        $this->tableAlias = $tableAlias;
        $this->tableMap = $tableMap;
    }

    public function __set(string $name, mixed $value): void
    {
        $resolvedName = $this->getRelationManager()->resolveRealName(
            $this,
            $name,
            $this->tableAlias,
            $this->tableMap,
            static::RELATIONSHIPS,
        );
        if (str_contains($resolvedName, '.')) {
            $this->handleRelationshipField($resolvedName, $value);
            return;
        }

        $this->handleMainEntityField($resolvedName, $value);
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
            $this->pendingData = [];
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
        ?FormFieldMappingPayloadInterface $fieldMapping = null,
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

    public function table(?string $default = null): string
    {
        return $this->getMapper()->getTableName($this, $default);
    }

    public function getRelationClassName(string $relationBdName): ?string
    {
        $relationships = $this->getRelationships();
        return $relationships[$relationBdName] ?? null;
    }

    public function getRelationPropertyName(string $officialKey): string
    {
        $camel = StringUtils::snakeCaseToCamelCase($officialKey);
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

    public function getFormat(): ?DisplayFormat
    {
        return $this->getMapper()->getFormat($this);
    }

    public function hasProperty(string $propertyName): bool
    {
        return $this->getMapper()->hasProperty($this, $propertyName);
    }

    public function entityKeyIsInitialzed(): bool
    {
        $keyProperty = $this->getEntityKeyProperty();
        return $this->isInitialized($keyProperty);
    }

    public function hasChanges(): bool
    {
        return $this->getChangeTracker()->hasChanges($this);
    }

    public function getEntityPrimarykeyValue(): mixed
    {
        $keyProperty = $this->getEntityKeyProperty();
        return $this->getFieldValue($keyProperty);
    }

    public function unsetEntityPrimaryKey(): void
    {
        $this->getMapper()->unsetEntityPrimaryKey($this);
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
    public function deobsfuscated(string $field, mixed $value): ?int
    {
        $property = $this->getProperty($field);
        $normalizer = $this->getNormalizer();

        $rawId = $normalizer->normalizeForClientToEntity($value, $property, $this);

        if ($rawId === null) {
            return $value;
        }
        return is_numeric($rawId) ? (int) $rawId : $rawId;
    }

    public function getDirtyData(): array
    {
        return $this->getHydrator()->getDirtyData($this);
    }

    public function track(): self
    {
        $this->getChangeTracker()->track($this);
        $this->_isTracking = true;
        return $this;
    }

    public function isTracking(): bool
    {
        return $this->_isTracking;
    }

    public function stopTracking(): void
    {
        $this->getChangeTracker()->stopTracking($this);
        $this->_isTracking = false;
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

    public function hasSoftDelete(): bool
    {
        return $this instanceof SoftDeletableInterface;
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

    public function getRelationshipClass(string $relationshipName): ?string
    {
        return static::RELATIONSHIPS[$relationshipName]['class'] ?? null;
    }

    public function isRelationshipCollection(string $relationshipName): bool
    {
        $config = static::RELATIONSHIPS[$relationshipName] ?? [];
        return ($config['collection'] ?? false) || ($config['type'] ?? '') === 'one-to-many';
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

    public function getPresenterFactory(): TypePresenterFactoryInterface
    {
        return $this->dependencies->getTypePresenterFactory();
    }

    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->dependencies->getChangeTracker();
    }

    public function prepareRowHydration(): void
    {
        $this->getRelationManager()->resetCurrentPointers($this->relatedEntities);
    }

    /**
     * @return EntityDependenciesFactoryInterface
     */
    public function getDependencies(): EntityDependenciesFactoryInterface
    {
        return $this->dependencies;
    }

    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->dependencies->getNormalizer();
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

    protected function getTypePresenterFactory(): TypePresenterFactory
    {
        return $this->dependencies->getTypePresenterFactory();
    }

    private function findRelationshipFromPath(array $pathParts): ?array
    {
        $currentPath = '';
        $relationships = static::RELATIONSHIPS;

        foreach ($pathParts as $part) {
            $currentPath = $currentPath ? $currentPath . '.' . $part : $part;

            if (isset($relationships[$currentPath])) {
                $config = $relationships[$currentPath];
                $remainingPath = implode('.', array_slice($pathParts, count(explode('.', $currentPath))));

                return [
                    'name' => $currentPath,
                    'remaining' => $remainingPath,
                    'config' => $config,
                ];
            }
        }

        return null;
    }

    private function handleMainEntityField(string $fieldName, mixed $value): void
    {
        $fieldToPropertyMap = $this->getMapper()->getFieldToPropertyMap($this);

        if (isset($fieldToPropertyMap[$fieldName])) {
            $this->getHydrator()->denormalizeAndSetProperty($this, $fieldName, $value);
            return;
        }

        // Check if fieldName itself is a property (camelCase conversion)
        $propertyName = $this->convertToPropertyName($fieldName);
        if ($this->hasProperty($propertyName)) {
            $this->getHydrator()->denormalizeAndSetProperty($this, $fieldName, $value);
            return;
        }

        // Unknown field, store for later
        $this->pendingData[$fieldName] = $value;
    }

    private function handleRelationshipField(string $fullPath, mixed $value): void
    {
        $parts = explode('.', $fullPath);
        $relationshipInfo = $this->findRelationshipFromPath($parts);

        if (!$relationshipInfo) {
            // Not a known relationship, store for later
            $this->pendingData[$fullPath] = $value;
            return;
        }

        $this->getRelationManager()->hydrateRelatedEntity(
            entity: $this,
            dbRelationName: $relationshipInfo['name'],
            field: $relationshipInfo['remaining'],
            value: $value,
            tableAlias: $this->tableAlias,
            tableMap: $this->tableMap,
            relatedEntities: $this->relatedEntities,
            relationshipConfig: $relationshipInfo['config'],
        );
    }
}