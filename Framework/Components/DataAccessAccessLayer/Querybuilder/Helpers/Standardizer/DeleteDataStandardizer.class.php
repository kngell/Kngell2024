<?php

declare(strict_types=1);

final class DeleteDataStandardizer extends AbstractDataStandardizer
{
    public function standardize(array $data): SqlGenericDataPayload
    {
        $data = $this->getRealData($data);
        return match (true) {
            $this->method === 'from' => $this->standardizeFrom($data),
            $this->method === 'where' => $this->standardizeConditions($data),
            default => throw new InvalidArgumentException("Unsupported insert method: {$this->method}")
        };
    }

    public function getContext(): string
    {
        return 'delete';
    }

    private function standardizeFrom(array $data): SqlGenericDataPayload
    {
        if (empty($data['table'])) {
            throw new BadQueryArgumentException('DELETE/FROM expects a table name');
        }
        return new SqlGenericDataPayload($data, $this->method);
    }
}