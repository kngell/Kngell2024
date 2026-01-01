<?php

declare(strict_types=1);

class ProcessedInsertData
{
    public function __construct(
        private array $data,
        private ?array $explicitColumns,
        private string $sourceType,
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getExplicitColumns(): ?array
    {
        return $this->explicitColumns;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function hasExplicitColumns(): bool
    {
        return !empty($this->explicitColumns);
    }

    public function isBatch(): bool
    {
        return ArrayUtils::isMultidimentional($this->data) &&
               ArrayUtils::isSequential($this->data) &&
               count($this->data) > 1;
    }

    public function getFirstRow(): array|Entity|CollectionInterface
    {
        return $this->data[0] ?? [];
    }

    public function getDerivedColumns(): array
    {
        if ($this->hasData()) {
            $firstRow = $this->getFirstRow();

            // Handle Entity object
            if ($firstRow instanceof Entity) {
                return array_keys($firstRow->toArray());
            }

            // Handle CollectionInterface
            if ($firstRow instanceof CollectionInterface) {
                $firstItem = $firstRow->first();
                return $firstItem instanceof Entity ? array_keys($firstItem->toArray()) : [];
            }

            // Handle array of Entities
            if (ArrayUtils::isObjectList($firstRow) && $firstRow[0] instanceof Entity) {
                return array_keys($firstRow[0]->toArray());
            }

            // Handle regular associative array
            if (is_array($firstRow)) {
                return array_keys($firstRow);
            }
        }

        return [];
    }

    public function getFinalColumns(): array
    {
        // Explicit columns take precedence
        if ($this->hasExplicitColumns()) {
            return $this->explicitColumns;
        }

        // Otherwise use columns derived from data
        return $this->getDerivedColumns();
    }

    public function validateForInsert(): void
    {
        // Check for duplicate data definition
        $this->validateNoDuplicateData();

        // We must have either data or explicit columns
        if (!$this->hasData() && !$this->hasExplicitColumns()) {
            throw new QueryFlowException(
                'INSERT query has no data and no columns specified. ' .
                'Source type: ' . $this->sourceType,
            );
        }

        // If we have both data and explicit columns, they must be consistent
        if ($this->hasData() && $this->hasExplicitColumns()) {
            $this->validateColumnDataConsistency();
        }

        // For values_only source with simple values, we need explicit columns
        if ($this->sourceType === 'values_only' &&
            !$this->hasExplicitColumns() &&
            $this->hasData() &&
            !ArrayUtils::isAssoc($this->getFirstRow())) {
            throw new QueryFlowException(
                'Simple values provided but no columns specified. ' .
                'Use columns() method or provide associative data in insert()/values().',
            );
        }
    }

    public function getValuesForBinding(): array
    {
        if (!$this->hasData()) {
            return [];
        }

        if ($this->isBatch()) {
            $flattened = [];
            foreach ($this->data as $row) {
                $flattened = array_merge($flattened, array_values($row));
            }
            return $flattened;
        }

        return array_values($this->getFirstRow());
    }

    private function validateNoDuplicateData(): void
    {
        // If we have data from multiple sources, it's an error
        $dataSources = [];

        if ($this->sourceType === 'insert_data' && $this->hasData()) {
            $dataSources[] = 'insert()';
        }

        if ($this->sourceType === 'values_only' && $this->hasData()) {
            $dataSources[] = 'values()';
        }

        if ($this->sourceType === 'entity_manager' && $this->hasData()) {
            $dataSources[] = 'EntityManager';
        }

        if (count($dataSources) > 1) {
            throw new QueryFlowException(
                'Duplicate data definition from multiple sources: ' .
                implode(' and ', $dataSources) .
                '. Use only one data source.',
            );
        }
    }

    private function validateColumnDataConsistency(): void
    {
        $expectedColumns = $this->explicitColumns;
        $actualColumns = $this->getDerivedColumns();

        if ($expectedColumns !== $actualColumns) {
            throw new InvalidArgumentException(
                "Explicit columns don't match data columns. " .
                'Expected: ' . implode(', ', $expectedColumns) . ', ' .
                'Got: ' . implode(', ', $actualColumns),
            );
        }
    }
}