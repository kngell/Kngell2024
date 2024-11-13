<?php

declare(strict_types=1);
abstract class AbstractCrud implements CrudInterface
{
    /**
     *@inheritDoc
     */
    public function lastID(): int
    {
        return $this->dataMapper->getLasID();
    }

    /**
     *@inheritDoc
     */
    public function getSchema(): string
    {
        return (string) $this->tableSchema;
    }

    /**
     *@inheritDoc
     */
    public function getSchemaID(): string
    {
        return (string) $this->tableSchemaID;
    }

    protected function recursive_query(array $options = []) : string
    {
        $arg = [
            'table' => $this->getSchema(),
            'type' => 'select',
            'selectors' => $options['recursive']['selectors'],
            'conditions' => $options['recursive']['conditions'],
            'params' => $options['recursive']['parameters'],
            'extras' => $options['recursive']['options'],
        ];
        list($sql, $query) = $this->queryBuilder->buildQuery($arg)->baseQuery();

        return $query;
    }
}
