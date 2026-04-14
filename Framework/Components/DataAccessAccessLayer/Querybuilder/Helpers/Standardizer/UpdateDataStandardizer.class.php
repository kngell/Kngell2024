<?php

declare(strict_types=1);

final class UpdateDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): SqlGenericDataPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new SqlGenericDataPayload();
        }
        return match (true) {
            $this->method === 'set' => $this->standardizeSet($data),
            $this->method === 'setColumns' => $this->standardizeSetColumns($data),
            $this->method === 'setValues' => $this->standardizeSetValues($data),
            in_array($this->method, ['where']) => $this->standardizeConditions($data),
            default => throw new InvalidArgumentException("Unsupported insert method: {$this->method}")
        };
    }

    public function getContext(): string
    {
        return 'update';
    }

    protected function standardizeConditions(array $data): SqlGenericDataPayload
    {
        return new SqlGenericDataPayload($data, $this->method);
    }

    private function standardizeSet(array $data): SqlGenericDataPayload
    {
        if (ArrayUtils::isLikeKeyValuePair($data)) {
            $data = $this->toAssoc($data);
        }
        if (ArrayUtils::isSequential($data)) {
            if (!ArrayUtils::isObjectList($data) || !ArrayUtils::isArrayList($data)) {
                throw new BadQueryArgumentException('UPDATE/SET expects an object or array of column => value pairs');
            }
        }

        if (!ArrayUtils::isAssoc($data)) {
            throw new BadQueryArgumentException(
                'UPDATE/SET expects an associative array of column => value pairs',
            );
        }
        return new SqlGenericDataPayload($data, $this->method);
    }

    private function standardizeSetColumns(array $data): SqlGenericDataPayload
    {
        if (!ArrayUtils::isStringList($data)) {
            throw new BadQueryArgumentException('UPDATE/SET_COLUMNS expects a list of columns');
        }
        return new SqlGenericDataPayload($data, $this->method);
    }

    private function standardizeSetValues(array $data): SqlGenericDataPayload
    {
        if (ArrayUtils::isSequential($data) && !isset($this->map['setColumns'])) {
            throw new BadQueryArgumentException('UPDATE/SET_VALUES requires SET_COLUMNS to be called first');
        }
        if (ArrayUtils::isAssoc($data)) {
            throw new BadQueryArgumentException('UPDATE/SET_VALUES expects a sequential array of values');
        }
        if (count($this->map['setColumns']) !== count($data)) {
            throw new BadQueryArgumentException('The number of columns and values must match');
        }
        return new SqlGenericDataPayload($data, $this->method);
    }
}
