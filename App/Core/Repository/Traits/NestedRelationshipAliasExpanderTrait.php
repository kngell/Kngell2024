<?php

declare(strict_types=1);

trait NestedRelationshipAliasExpanderTrait
{
    public function expandTableAliasForNestedRelationships(
        array $collectionRelationshipMap,
    ): self {
        $queryBuilder = $this->em->getQueryBuilder();
        if (!$queryBuilder) {
            return $this;
        }

        $currentTableAlias = $queryBuilder->getTableAlias();
        $currentTableMap = $queryBuilder->getLogicalToPhysicalMap();
        $expandedTableAlias = $currentTableAlias;
        $expandedTableMap = $currentTableMap;

        foreach ($collectionRelationshipMap as $relationshipName => $nestedEntityClass) {
            $this->expandForRelationship(
                $nestedEntityClass,
                $relationshipName,
                $currentTableAlias,
                $currentTableMap,
                $expandedTableAlias,
                $expandedTableMap,
            );
        }

        $queryBuilder->setTableAlias($expandedTableAlias);
        $queryBuilder->setLogicalToPhysicalMap($expandedTableMap);

        return $this;
    }

    private function expandForRelationship(
        string $entityClass,
        string $relationshipPrefix,
        array $currentTableAlias,
        array $currentTableMap,
        array &$expandedTableAlias,
        array &$expandedTableMap,
    ): void {
        if (!defined($entityClass . '::RELATIONSHIPS')) {
            return;
        }

        $relationships = $entityClass::RELATIONSHIPS;

        foreach ($relationships as $nestedRelationshipName => $nestedEntityClass) {
            foreach ($currentTableAlias as $key => $alias) {
                if ($this->matchesRelationship($key, $nestedRelationshipName)) {
                    $prefixedKey = $relationshipPrefix . '.' . $nestedRelationshipName;
                    $expandedTableAlias[$prefixedKey] = $alias;
                    if (isset($currentTableMap[$key])) {
                        $expandedTableMap[$prefixedKey] = $currentTableMap[$key];
                    }
                    break;
                }
            }
        }
    }

    private function matchesRelationship(string $key, string $relationshipName): bool
    {
        return str_contains($key, '|' . $relationshipName) ||
               str_ends_with($key, $relationshipName) ||
               str_ends_with($key, '.' . $relationshipName);
    }
}