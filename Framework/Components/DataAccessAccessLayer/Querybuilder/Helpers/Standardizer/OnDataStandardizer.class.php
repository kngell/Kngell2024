<?php

declare(strict_types=1);

class OnDataStandardizer extends AbstractDataStandardizer
{
    private ?string $logicalTable = null;
    private ?TablesAliasHelper $helper = null;

    public function standardize(array $data): OnPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new OnPayload();
        }

        $data = $this->normalizeMultidimensional($data);
        $standardized = $this->standardizeOnConditions($data);

        return new OnPayload($standardized);
    }

    public function getContext(): string
    {
        return 'on';
    }

    public function setHelper(TablesAliasHelper $helper): self
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * @param null|string $logicalTable
     *
     * @return self
     */
    public function setLogicalTable(?string $logicalTable): self
    {
        $this->logicalTable = $logicalTable;

        return $this;
    }

    private function normalizeMultidimensional(array $data): array
    {
        if (count($data) === 1 && ArrayUtils::isMultidimentional($data)) {
            $first = ArrayUtils::first($data);
            if (is_array($first) && !ArrayUtils::isStringList($first)) {
                return $this->normalizeMultidimensional($first);
            }
            return $first;
        }
        return $data;
    }

    private function standardizeOnConditions(array $data): array
    {
        $format = $this->detectFormat($data);

        return match ($format) {
            'associative' => $this->standardizeAssociative($data),
            'pair_list' => $this->standardizePairList($data),
            'column_list' => $this->standardizeColumnList($data),
            'nested' => $this->standardizeNested($data),
            default => $this->standardizeDefault($data)
        };
    }

    private function detectFormat(array $data): string
    {
        if (empty($data)) {
            return 'empty';
        }
        if (ArrayUtils::isAssoc($data)) {
            return 'associative';
        }
        if ($this->isPairList($data)) {
            return 'pair_list';
        }
        if (ArrayUtils::isStringList($data)) {
            return 'column_list';
        }
        if (ArrayUtils::isMultidimentional($data)) {
            return 'nested';
        }

        return 'unknown';
    }

    private function standardizeAssociative(array $assocData): array
    {
        $result = [];
        foreach ($assocData as $left => $right) {
            $result[] = $this->processCondition($left);
            $result[] = $this->processCondition($right);
        }
        return $result;
    }

    private function standardizePairList(array $pairList): array
    {
        $result = [];
        foreach ($pairList as $pair) {
            if (ArrayUtils::isAssoc($pair)) {
                $key = array_key_first($pair);
                $result[] = $this->processCondition($key);
                $result[] = $this->processCondition($pair[$key]);
            } else {
                $result[] = $this->processCondition($pair[0]);
                $result[] = $this->processCondition($pair[1]);
            }
        }
        return $result;
    }

    private function standardizeColumnList(array $columnList): array
    {
        $result = [];
        foreach ($columnList as $column) {
            $result[] = $this->processCondition($column);
        }
        return $result;
    }

    private function standardizeNested(array $nestedData): array
    {
        $result = [];
        foreach ($nestedData as $item) {
            if (is_array($item)) {
                $nestedResult = $this->standardizeOnConditions($item);
                if (!empty($nestedResult)) {
                    $result[] = $nestedResult;
                }
            } else {
                $result[] = $this->processCondition($item);
            }
        }
        return $result;
    }

    private function standardizeDefault(array $data): array
    {
        if (ArrayUtils::isSequential($data)) {
            return array_map([$this, 'processCondition'], $data);
        }

        return $data;
    }

    private function processCondition(string $condition): string
    {
        if (!$this->helper || !$this->logicalTable || !str_contains($condition, '.')) {
            return $condition;
        }

        $parts = explode('.', $condition);
        $tableName = $parts[0];
        $column = $parts[1];

        $currentPhysicalTable = $this->helper->getPhysicalTable($this->logicalTable);
        $conditionPhysicalTable = $this->helper->getPhysicalTable($tableName);

        if ($conditionPhysicalTable === $currentPhysicalTable) {
            return $this->logicalTable . '.' . $column;
        }

        return $condition;
    }
}