<?php

declare(strict_types=1);

final class InsertDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): InsertPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new InsertPayload([], []);
            // throw new InvalidArgumentException('Insert data cannot be empty');
        }

        return match ($this->method) {
            'insert' => $this->standardizeInsert($data),
            'columns' => $this->standardizeColumns($data),
            'values' => $this->standardizeValues($data),
            default => throw new InvalidArgumentException("Unsupported insert method: {$this->method}")
        };
    }

    public function getContext(): string
    {
        return 'insert';
    }

    private function standardizeInsert(array $data): InsertPayload
    {
        // 1️⃣ Columns only
        if (ArrayUtils::isStringList($data)) {
            return new InsertPayload($data, []);
        }

        // 2️⃣ Associative array
        if (ArrayUtils::isAssoc($data)) {
            return new InsertPayload(array_keys($data), array_values($data));
        }

        // 3️⃣ Flat key/value list
        if (ArrayUtils::isSequentialKeyValueList($data)) {
            $assoc = $this->toAssoc($data);
            return new InsertPayload(array_keys($assoc), array_values($assoc));
        }

        // 4️⃣ Pair list ([[col,val],[col=>val],...] or multi-row)
        if ($this->isPairList($data)) {
            // Normalize all rows into multi-row values
            $columns = null;
            $values = [];

            foreach ($data as $row) {
                $assoc = ArrayUtils::isAssoc($row) ? $row : [$row[0] => $row[1]];
                if ($columns === null) {
                    $columns = array_keys($assoc);
                }
                $values[] = array_values($assoc);
            }

            return new InsertPayload($columns ?? [], $values);
        }

        // Reject values-only
        if (ArrayUtils::isSequential($data)) {
            throw new InvalidArgumentException(
                'insert() does not accept values-only arrays. Use values() instead.',
            );
        }

        throw new InvalidArgumentException('Unsupported insert() data format');
    }

    private function standardizeColumns(array $data): InsertPayload
    {
        $insertData = isset($this->map['insert']) ? $this->map['insert'] : null;
        if (!empty($insertData)) {
            throw new InvalidArgumentException('Insert data already has data defined.');
        }
        if (!ArrayUtils::isStringList($data)) {
            throw new InvalidArgumentException('Invalid column names');
        }
        return new InsertPayload($data, []);
    }

    private function standardizeValues(array $data): InsertPayload
    {
        $insertData = isset($this->map['insert']) ? $this->map['insert'] : null;
        $columnsData = isset($this->map['columns']) ? $this->map['columns'] : null;

        if ($insertData->hasColumns() || $insertData->hasValues()) {
            throw new InvalidArgumentException('Insert data already has data defined.');
        }
        // Columns already known → values only
        if (isset($columnsData) && ArrayUtils::isSequential($data)) {
            return new InsertPayload($this->map['columns'], $data);
        }

        // Infer columns from associative or pair-list rows
        if (ArrayUtils::isAssoc($data)) {
            if ($insertData === null && $columnsData === null) {
                return new InsertPayload(array_keys($data), [$data]);
            }
        }

        if ($this->isPairList($data)) {
            $columns = [];
            $values = [];
            foreach ($data as $row) {
                $assoc = ArrayUtils::isAssoc($row) ? $row : [$row[0] => $row[1]];
                if (empty($columns)) {
                    $columns = array_keys($assoc);
                }
                $values[] = array_values($assoc);
            }
            return new InsertPayload($columns, $values);
        }

        if (ArrayUtils::isSequentialKeyValueList($data)) {
            $assoc = $this->toAssoc($data);
            return new InsertPayload(array_keys($assoc), [$assoc]);
        }

        // Reject columns-only
        if (ArrayUtils::isStringList($data)) {
            throw new InvalidArgumentException(
                'values() does not accept columns-only arrays. Use insert() or columns() instead.',
            );
        }

        throw new InvalidArgumentException('Unsupported VALUES data format');
    }
}
