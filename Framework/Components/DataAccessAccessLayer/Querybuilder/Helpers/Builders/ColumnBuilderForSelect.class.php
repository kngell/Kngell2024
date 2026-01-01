<?php

declare(strict_types=1);

class ColumnBuilderForSelect
{
    public function __construct(
        private bool $selectAsAlias,
    ) {
    }

    public function build(string $column, string $tableAlias): string
    {
        if (ColumnTypeDetector::isComplexExpression($column)) {
            return $this->buildComplexColumn($column, $tableAlias);
        }

        if (ColumnTypeDetector::isCountFunction($column)) {
            return $this->buildCountColumn($column, $tableAlias);
        }

        if (ColumnTypeDetector::isFunctionCall($column)) {
            return $this->buildFunctionColumn($column, $tableAlias);
        }

        return $this->buildSimpleColumn($column, $tableAlias);
    }

    private function buildSimpleColumn(string $column, string $tableAlias): string
    {
        if (!$this->isValidColumnName($column)) {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }

        if ($column === '*') {
            return $tableAlias . '.' . $column;
        }

        if ($this->selectAsAlias) {
            $finalAlias = $this->customAliases[$column] ?? $tableAlias . '_' . $column;
            return $tableAlias . '.' . $column . ' AS ' . $finalAlias;
        }

        return $tableAlias . '.' . $column;
    }

    private function buildComplexColumn(string $column, string $tableAlias): string
    {
        if (ColumnTypeDetector::isCountFunction($column)) {
            return $this->buildCountColumn($column, $tableAlias);
        }
        return $column;
    }

    private function buildCountColumn(string $column, string $alias): string
    {
        list($AS, $cleanColumn) = $this->extractAsAlias($column);

        if (preg_match('#count\((.*?)\)#i', $cleanColumn, $matches)) {
            $innerColumn = $matches[1];
            if ($this->isSimpleColumn($innerColumn)) {
                $innerColumn = $alias . '.' . $innerColumn;
            }
            $result = 'COUNT(' . $innerColumn . ')';
            return $result . (!empty($AS) ? ' AS ' . $AS : '');
        }

        return $this->buildSimpleColumn($column, $alias);
    }

    private function buildFunctionColumn(string $column, string $tableAlias): string
    {
        list($AS, $cleanColumn) = $this->extractAsAlias($column);

        if (preg_match('#([a-z_]+)\((.*?)\)#i', $cleanColumn, $matches)) {
            $function = strtoupper($matches[1]);
            $innerColumn = $matches[2];

            if ($this->isSimpleColumn($innerColumn)) {
                $innerColumn = $tableAlias . '.' . $innerColumn;
            }

            $result = $function . '(' . $innerColumn . ')';

            if ($this->selectAsAlias && empty($AS)) {
                $AS = $tableAlias . '_' . $function . '_' . str_replace(['.', '(', ')'], '_', $innerColumn);
            }

            return $result . (!empty($AS) ? ' AS ' . $AS : '');
        }

        return $column;
    }

    private function extractAsAlias(string $column): array
    {
        $AS = '';
        $parts = explode(' as ', strtolower($column));
        if (count($parts) === 2) {
            $AS = trim($parts[1]);
            $column = trim($parts[0]);
        }
        return [$AS, $column];
    }

    private function isValidColumnName(string $column): bool
    {
        return ColumnStandardizer::isValidColumnName($column);
    }

    private function isSimpleColumn(string $value): bool
    {
        return ColumnTypeDetector::isSimpleColumn($value);
    }
}