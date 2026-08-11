<?php

declare(strict_types=1);

class ColumnBuilderForSelect
{
    public function __construct(
        private bool $selectAsAlias,
        private bool $distinctCount,
    ) {
    }

    public function build(
        string|SqlQueryBuilderInterface $column,
        TablesAliasHelper $helper,
        QueryState &$state,
        string $logicalKey,
        ?string $customAlias = null,
    ): string {
        $columnTrimmed = trim($column);

        if ($this->isLiteralConstant($columnTrimmed)) {
            return $column;
        }

        $tableAlias = $state->tableAlias ?? [];
        $aliasCheck = $state->aliasCheck ?? [];
        $helper->setCustomAlias($customAlias);

        // ---------------------------------------------------------
        // STEP 1: DECONSTRUCT EXPRESSION PREFIXES (The Core Fix)
        // ---------------------------------------------------------
        if (str_contains($columnTrimmed, '.') && !ColumnTypeDetector::isComplexExpression($columnTrimmed)) {
            $lastDotPos = strrpos($columnTrimmed, '.');
            $possiblePrefix = substr($columnTrimmed, 0, $lastDotPos);
            $remainingExpression = substr($columnTrimmed, $lastDotPos + 1);
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $possiblePrefix)) {
                $logicalKey = $possiblePrefix;
                $columnTrimmed = trim($remainingExpression);
            }
        }

        list($table, $resolvedHelperAlias) = $helper->get($logicalKey, $tableAlias, $aliasCheck);

        $currentAlias = $customAlias
            ?? $tableAlias[$logicalKey]
            ?? $resolvedHelperAlias;

        if ($currentAlias === null) {
            $currentAlias = $logicalKey;
        }
        $state->tables[$logicalKey][] = $columnTrimmed;
        // ---------------------------------------------------------
        // STEP 2: ROUTE NORMALIZED EXPRESSIONS
        // ---------------------------------------------------------

        if ($columnTrimmed === '*') {
            return $this->buildSimpleColumn($columnTrimmed, $currentAlias);
        }

        if (ColumnTypeDetector::isComplexExpression($columnTrimmed)) {
            $runtimeAliasMap = $tableAlias;
            $runtimeAliasMap[$logicalKey] = $currentAlias;

            $parser = new SqlExpressionParser($columnTrimmed);
            return $parser->parseAndBuild($logicalKey, $this->selectAsAlias, $runtimeAliasMap);
        }

        if (ColumnTypeDetector::isCountFunction($columnTrimmed)) {
            return $this->buildCountColumn($columnTrimmed, $currentAlias);
        }

        if (ColumnTypeDetector::isFunctionCall($columnTrimmed)) {
            return $this->buildFunctionColumn($columnTrimmed, $currentAlias);
        }
        return $this->buildSimpleColumn($columnTrimmed, $currentAlias);
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
            $finalAlias = $tableAlias . '_' . $column;
            return $tableAlias . '.' . $column . ' AS ' . $finalAlias;
        }

        return $tableAlias . '.' . $column;
    }

    private function buildCountColumn(string $column, string $alias): string
    {
        list($AS, $cleanColumn) = $this->extractAsAlias($column);

        if (preg_match('#count\((.*?)\)#i', $cleanColumn, $matches)) {
            $innerColumn = trim($matches[1]);
            if (ColumnTypeDetector::isSimpleColumn($innerColumn)) {
                $innerColumn = $alias . '.' . $innerColumn;
            }
            $result = $this->distinctCount ? 'COUNT(DISTINCT ' . $innerColumn . ')' : 'COUNT(' . $innerColumn . ')';
            return $result . (!empty($AS) ? ' AS ' . $AS : '');
        }

        return $this->buildSimpleColumn($column, $alias);
    }

    private function buildFunctionColumn(string $column, string $tableAlias): string
    {
        list($AS, $cleanColumn) = $this->extractAsAlias($column);

        if (preg_match('#([a-z_]+)\((.*?)\)#i', $cleanColumn, $matches)) {
            $function = strtoupper($matches[1]);
            $innerColumn = trim($matches[2]);

            if (ColumnTypeDetector::isSimpleColumn($innerColumn)) {
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
        $parts = preg_split('/\s+as\s+/i', $column);
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

    private function isLiteralConstant(string $value): bool
    {
        $value = trim($value);
        if (is_numeric($value)) {
            return true;
        }
        $parts = preg_split('/\s+as\s+/i', $value);
        $source = trim($parts[0]);
        return is_numeric($source) || preg_match('/^\'.*\'$/s', $source) || preg_match('/^".*"$/s', $source) || in_array(strtoupper($source), ['NULL', 'TRUE', 'FALSE'], true);
    }
}