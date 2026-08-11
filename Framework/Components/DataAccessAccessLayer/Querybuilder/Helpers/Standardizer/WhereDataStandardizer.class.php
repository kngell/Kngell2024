<?php

declare(strict_types=1);

class WhereDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): SqlGenericDataPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            return new SqlGenericDataPayload();
        }
        return new SqlGenericDataPayload($data, $this->method);
    }

    public function getContext(): string
    {
        return 'where';
    }
}