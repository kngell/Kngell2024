<?php

declare(strict_types=1);

final class InsertDataStandardizerOLD1 extends AbstractDataStandardizer
{
    public function standardize(array $data): SqlGenericDataPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new SqlGenericDataPayload([], $this->method);
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

    private function standardizeInsert(array $data): SqlGenericDataPayload
    {
        // 1️⃣ Columns only
        if (ArrayUtils::isStringList($data)) {
            return new SqlGenericDataPayload($data, $this->method);
        }

        // 2️⃣ Associative array
        if (ArrayUtils::isAssoc($data)) {
            return new SqlGenericDataPayload($data, $this->method);
        }

        // 3️⃣ Flat key/value list
        if (ArrayUtils::isKeyValueList($data)) {
            $assoc = $this->toAssoc($data);
            return new SqlGenericDataPayload($assoc, $this->method);
        }

        // 4️⃣ Pair list ([[col,val],[col=>val],...] or multi-row)
        if ($this->isPairList($data)) {
            $data = $this->fromPairListToAssoc($data);
            return new SqlGenericDataPayload($data, $this->method);
        }

        // Reject values-only
        if (ArrayUtils::isSequential($data)) {
            throw new InvalidArgumentException(
                'insert() does not accept values-only arrays. Use values() instead.',
            );
        }

        throw new InvalidArgumentException('Unsupported insert() data format');
    }

    private function standardizeColumns(array $data): SqlGenericDataPayload
    {
        // Values already known → columns only
        if (ArrayUtils::isStringList($data)) {
            return new SqlGenericDataPayload($data, $this->method);
        }
        throw new InvalidArgumentException('All columns must be strings');
    }

    private function standardizeValues(array $data): SqlGenericDataPayload
    {
        // Columns already known → values only
        if (ArrayUtils::isSequential($data)) {
            return new SqlGenericDataPayload($data, $this->method);
        }

        // Infer columns from associative or pair-list rows
        if (ArrayUtils::isAssoc($data)) {
            return new SqlGenericDataPayload($data, $this->method);
        }

        if ($this->isPairList($data)) {
            $data = $this->fromPairListToAssoc($data);
            return new SqlGenericDataPayload($data, $this->method);
        }

        if (ArrayUtils::isKeyValueList($data)) {
            $assoc = $this->toAssoc($data);
            return new SqlGenericDataPayload($assoc, $this->method);
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
