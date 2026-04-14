<?php

declare(strict_types=1);

class WhereDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): SqlGenericDataPayload
    {
        $data = $this->getRealData($data);

        if (empty($data)) {
            throw new BadQueryArgumentException('WHERE condition requires at least one condition');
        }
        return new SqlGenericDataPayload($data, $this->method);
    }

    public function getContext(): string
    {
        return 'where';
    }
}