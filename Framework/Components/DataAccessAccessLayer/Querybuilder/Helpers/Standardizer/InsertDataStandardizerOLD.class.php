<?php

declare(strict_types=1);

class InsertDataStandardizerOLD extends AbstractDataStandardizer
{
    protected const string CONTEXT = 'insert';

    private array $insertMap;

    public function __construct(
        private array $formats = ['associative', 'key_value_list', 'column_list', 'values_list'],
    ) {
    }

    public function standardize(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $data = $this->normalizeMultidimensional($data);
        $format = $this->detectFormat($data);
        if ($format === 'column_list' && $this->method === 'values') {
            return $this->toAssoc($data);
        }

        return match ($format) {
            'associative' => $data,
            'key_value_list' => $this->toAssoc($data),
            'column_list' => $data,
            'values_list' => $data,
            'single_row' => $data,
            default => throw new InvalidArgumentException("Unsupported data format: $format")
        };
    }

    public function getDetectedFormat(array $data): string
    {
        return $this->detectFormat($data);
    }

    /**
     * @param array $insertMap
     *
     * @return self
     */
    public function setMap(array $insertMap): self
    {
        $this->insertMap = $insertMap;

        return $this;
    }

    private function normalizeMultidimensional(array $data): array
    {
        if (ArrayUtils::isMultidimentional($data) && count($data) === 1) {
            $first = ArrayUtils::first($data);
            if (!ArrayUtils::isMultidimentional($first)) {
                return $first;
            } else {
                return $this->normalizeMultidimensional($first);
            }
        }
        return $data;
    }

    private function detectFormat(array $data): string
    {
        if (ArrayUtils::isAssoc($data)) {
            return 'associative';
        }
        if (ArrayUtils::isKeyValueList($data) && !isset($this->insertMap['columns'])) {
            if ($this->method === 'values' && isset($this->insertMap['insert']) && ArrayUtils::isStringList($this->insertMap['insert'])) {
                return 'values_list';
            }
            return ArrayUtils::isStringList($data) && $this->method === 'insert' ? 'column_list' : 'key_value_list';
        }

        if (ArrayUtils::isStringList($data)) {
            return $this->method === 'columns' ? 'column_list' : 'key_value_list';
        }

        if (ArrayUtils::isSequential($data) && $this->method === 'values') {
            return 'values_list';
        }
        if (ArrayUtils::isSequential($data)) {
            return 'single_row';
        }

        return 'unknown';
    }
}
