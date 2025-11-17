<?php

declare(strict_types=1);

class InsertDataStandardizer implements DataStandardizerInterface
{
    private string $context = 'values';
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

        // Use context to resolve ambiguity between column_list and key_value_list
        if ($format === 'column_list' && $this->context === 'values') {
            // In values context, treat even string lists as key/value pairs
            if ($this->couldBeKeyValue($data)) {
                return $this->convertKeyValueList($data);
            }
        }

        return match ($format) {
            'associative' => $data,
            'key_value_list' => $this->convertKeyValueList($data),
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

    public function setContext(string $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param array $insertMap
     *
     * @return self
     */
    public function setInsertMap(array $insertMap): self
    {
        $this->insertMap = $insertMap;

        return $this;
    }

    private function couldBeKeyValue(array $data): bool
    {
        return ArrayUtils::isStringList($data) &&
               count($data) % 2 === 0 &&
               count($data) >= 2;
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
        // 1. Check for associative array (always associative regardless of context)
        if (ArrayUtils::isAssoc($data)) {
            return 'associative';
        }

        // 2. Check for key/value list (even count with string keys)
        if ($this->isKeyValueList($data) && !isset($this->insertMap['columns'])) {
            if ($this->context === 'values' && isset($this->insertMap['insert']) && ArrayUtils::isStringList($this->insertMap['insert'])) {
                return 'values_list';
            }
            return ArrayUtils::isStringList($data) && $this->context === 'insert' ? 'column_list' : 'key_value_list';
        }

        // 3. Check for string list (all elements are strings)
        if (ArrayUtils::isStringList($data)) {
            return $this->context === 'columns' ? 'column_list' : 'key_value_list';
        }

        // 4. Check for values list (sequential, mixed types, in values context)
        if (ArrayUtils::isSequential($data) && $this->context === 'values') {
            return 'values_list';
        }

        // 5. Generic sequential array
        if (ArrayUtils::isSequential($data)) {
            return 'single_row';
        }

        return 'unknown';
    }

    private function isKeyValueList(array $data): bool
    {
        $count = count($data);

        // Must have even number of elements and at least 2
        if ($count % 2 !== 0 || $count < 2) {
            return false;
        }

        // Check that every even index (0, 2, 4...) is a string
        for ($i = 0; $i < $count; $i += 2) {
            if (!is_string($data[$i])) {
                return false;
            }
        }

        return true;
    }

    private function convertKeyValueList(array $keyValueList): array
    {
        $result = [];

        for ($i = 0; $i < count($keyValueList); $i += 2) {
            $key = (string) $keyValueList[$i];
            $value = $keyValueList[$i + 1];
            $result[$key] = $value;
        }

        return $result;
    }
}