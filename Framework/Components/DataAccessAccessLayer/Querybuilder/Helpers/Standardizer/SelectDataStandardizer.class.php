<?php

declare(strict_types=1);

class SelectDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): SelectPayload
    {
        $data = $this->getRealData($data);
        if (empty($data)) {
            return new SelectPayload();
        }

        $data = $this->normalizeMultidimensional($data);
        $format = $this->detectFormat($data);

        return match ($format) {
            'associative' => new SelectPayload($this->convertAssociativeToColumnList($data)),
            'column_list' ,'mixte_type' => new SelectPayload($data),
            // 'mixte_type' => new SelectPayload($this->normalizedData($data)),
            default => throw new InvalidArgumentException("Unsupported data format: $format")
        };
    }

    public function getContext(): string
    {
        return 'select';
    }

    private function normalizedData(array $data): array
    {
        $normalizedData = [];
        foreach ($data as $column) {
            if ($column instanceof SqlComponent) {
                $normalizedData[] = $column->build();
            } else {
                $normalizedData[] = $column;
            }
        }
        return $normalizedData;
    }

    private function normalizeMultidimensional(array $data): array
    {
        if (count($data) === 1 && ArrayUtils::isMultidimentional($data)) {
            $first = ArrayUtils::first($data);
            if (is_array($first) && !ArrayUtils::isStringList($first)) {
                return  $this->normalizeMultidimensional($first);
            } else {
                return $first;
            }
        } elseif (count($data) === 1 && isset($data[0]) && empty($data[0])) {
            return ArrayUtils::first($data);
        }
        return $data;
    }

    private function detectFormat(array $data): string
    {
        if (ArrayUtils::isAssoc($data)) {
            return 'associative';
        }

        if (ArrayUtils::isStringList($data)) {
            return 'column_list';
        }
        if (ArrayUtils::hasMixedTypes($data)) {
            return 'mixte_type';
        }

        return 'unknown';
    }

    private function convertAssociativeToColumnList(array $associativeData): array
    {
        $columnList = [];
        foreach ($associativeData as $alias => $column) {
            $columnList[] = is_numeric($alias) || $alias !== $column
                ? "{$column} AS {$alias}"
                : $column;
        }
        return $columnList;
    }
}