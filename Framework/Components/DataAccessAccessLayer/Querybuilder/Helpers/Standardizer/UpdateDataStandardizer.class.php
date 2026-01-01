<?php

declare(strict_types=1);

final class UpdateDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): UpdatePayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new UpdatePayload();
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

    private function standardizeSet(array $data): UpdatePayload
    {
        if (ArrayUtils::isSequential($data)) {
            $data = $this->toAssoc($data);
        }

        if (!ArrayUtils::isAssoc($data)) {
            throw new BadQueryArgumentException(
                'UPDATE/SET expects an associative array of column => value pairs',
            );
        }
        return new UpdatePayload($data, $this->method);
    }

    private function standardizeSetColumns(array $data): UpdatePayload
    {
        if (!ArrayUtils::isStringList($data)) {
            throw new BadQueryArgumentException('UPDATE/SET_COLUMNS expects a list of columns');
        }
        return new UpdatePayload($data, $this->method);
    }

    private function standardizeSetValues(array $data): UpdatePayload
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
        return new UpdatePayload($data, $this->method);
    }

    private function standardizeConditions(array $data): UpdatePayload
    {
        return new UpdatePayload($data, $this->method);
    }
}