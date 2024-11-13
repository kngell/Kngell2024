<?php

declare(strict_types=1);

use Throwable;

class Crud extends AbstractCrud implements CrudInterface
{
    /**
     * dataMapper Object.
     */
    protected DataMapper $dataMapper;
    /**
     * Query Builder.
     */
    protected queryBuilder $queryBuilder;
    /**
     * Table Name.
     */
    protected string $tableSchema;
    /**
     * Table Name primary key.
     */
    protected string $tableSchemaID;
    protected array $options;

    public function __construct(DataMapperInterface $dataMapper, queryBuilderInterface $queryBuilder, string $tableSchema, string $tableSchemaID, ?array $options = [])
    {
        $this->tableSchema = $tableSchema;
        $this->tableSchemaID = $tableSchemaID;
        $this->options = $options;
        $this->dataMapper = $dataMapper;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     *@inheritDoc
     */
    public function create(array $fields = []): DataMapperInterface
    {
        try {
            $arg = ['table' => $this->getSchema(), 'type' => 'insert', 'fields' => $fields];
            $query = $this->queryBuilder->buildQuery($arg)->insert();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($fields));
            if ($this->dataMapper->numrow() == 1) {
                return $this->dataMapper->results([], __FUNCTION__);
            }
        } catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * =====================================================================
     * Select data from data base
     * =====================================================================.
     *@inheritDoc
     */
    public function read(array $selectors = [], array $conditions = [], array $params = [], array $options = [])
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'select',
                'selectors' => $selectors,
                'conditions' => $conditions,
                'params' => $params,
                'extras' => $options,
                'recursive_query' => array_key_exists('recursive', $options) ? $this->recursive_query($options) : '',
            ];
            $query = $this->queryBuilder->buildQuery($arg)->select();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($arg['conditions'], $params));
            if ($this->dataMapper->numrow() > 0) {
                return $this->dataMapper->results($options, __FUNCTION__);
            }

            return $this->dataMapper;
        } catch (\Throwable $th) {
            throw new DataAccessLayerException($th->getMessage());
        }
    }

    public function showColumns(array $options) : object
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'show',
            ];
            $query = $this->queryBuilder->buildQuery($arg)->showColumn();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters([], []));
            if ($this->dataMapper->numrow() > 0) {
                return $this->dataMapper->results($options, __FUNCTION__);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Update data from data base
     * =====================================================================.
     *@inheritDoc
     */
    public function update(array $fields = [], array $conditions = []): DataMapperInterface
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'update',
                'fields' => $fields,
                'where' => $conditions,
            ];
            $query = $this->queryBuilder->buildQuery($arg)->update();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($conditions, $fields));
            if ($this->dataMapper->numrow() == 1) {
                return $this->dataMapper->results([], __FUNCTION__);
            }

            return 0;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Delete data from database
     * =====================================================================.
     *@inheritDoc
     */
    public function delete(array $conditions = []): DataMapperInterface
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'delete',
                'conditions' => $conditions,
            ];
            $query = $this->queryBuilder->buildQuery($arg)->delete();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($conditions));

            return $this->dataMapper->results([], __FUNCTION__);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     *@inheritDoc
     */
    public function search(array $selectors = [], array $searchconditions = [])
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'search',
                'selectors' => $selectors,
                'conditions' => $searchconditions,
            ];
            $query = $this->queryBuilder->buildQuery($arg)->search();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($searchconditions));
            if ($this->dataMapper->numrow() > 0) {
                return $this->dataMapper->results();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @inheritDoc
     */
    public function customQuery(string $query = '', ?array $conditions = []) : mixed
    {
        try {
            $arg = [
                'table' => $this->getSchema(),
                'type' => 'custom',
                'custom' => $query,
                'conditions' => $conditions,
            ];
            $query = $this->queryBuilder->buildQuery($arg)->customQuery();
            $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($conditions));
            if ($this->dataMapper->numrow() > 0) {
                return $this->dataMapper->results();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function aggregate(string $type, ?string $fields = 'id', array $conditions = [])
    {
        $args = ['table' => $this->getSchema(), 'primary_key' => $this->getSchemaID(), 'type' => 'select', 'aggregate' => $type, 'aggregate_field' => $fields, 'conditions' => $conditions, 'extras' => ['orderby' => '']];
        $query = $this->queryBuilder->buildQuery($args)->select();
        $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($conditions));
        if ($this->dataMapper->numrow() > 0) {
            return $this->dataMapper->column();
        }
    }

    public function countRecords(array $conditions = [], ?string $fields = 'id'): int
    {
        if ($this->getSchemaID() != '') {
            return empty($conditions) ? $this->aggregate('count', $this->getSchemaID()) : $this->aggregate('count', $this->getSchemaID(), $conditions);
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $selectors
     * @param array $conditions
     * @return object|null
     */
    public function get(array $selectors = [], array $conditions = []) : ?Object
    {
        $args = ['table' => $this->getSchema(), 'type' => 'select', 'selectors' => $selectors, 'conditions' => $conditions];
        $query = $this->queryBuilder->buildQuery($args)->select();
        $this->dataMapper->persist($query, $this->dataMapper->buildQueryParameters($conditions));
        if ($this->dataMapper->numrow() >= 0) {
            return $this->dataMapper->result();
        }
    }
}
