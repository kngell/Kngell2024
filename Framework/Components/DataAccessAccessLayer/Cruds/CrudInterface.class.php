<?php

declare(strict_types=1);

interface CrudInterface
{
    /**
     * --------------------------------------------------------------------------------------------------
     * Get Data base name.
     * @return string
     */
    public function getSchema():String;

    /**
     * --------------------------------------------------------------------------------------------------
     * Get Primary Key.
     * @return string
     */
    public function getSchemaID():String;

    /**
     * --------------------------------------------------------------------------------------------------
     * Get Last Insert ID.
     * @return int
     */
    public function lastID():Int;

    /**
     * --------------------------------------------------------------------------------------------------
     * Insert in data base successfully or not.
     * @param array $fields
     * @return DataMapperInterface
     */
    public function create(array $fields = []): DataMapperInterface;

    /**
     * --------------------------------------------------------------------------------------------------
     * Read data from data base.
     * @param array $selectors
     * @param array $conditions
     * @param array $params
     * @param array $options
     * @return mixed
     */
    public function read(array $selectors = [], array $conditions = [], array $params = [], array $options = []);

    /**
     * --------------------------------------------------------------------------------------------------
     * Update data.
     * @param array $fields
     * @param array $conditions
     * @return DataMapperInterface
     */
    public function update(array $fields = [], array $conditions = []) : DataMapperInterface;

    /**
     * --------------------------------------------------------------------------------------------------
     * Delete data.
     * @param array $conditions
     * @return DataMapperInterface
     */
    public function delete(array $conditions = []) :DataMapperInterface;

    /**
     * --------------------------------------------------------------------------------------------------
     * Search data.
     * @param array $selectors
     * @param array $searchconditions
     * @return mixed
     */
    public function search(array $selectors = [], array $searchconditions = []);

    /**
     * --------------------------------------------------------------------------------------------------
     * Custom Data.
     * @param array $query
     * @param array $conditions
     * @return void
     */
    public function customQuery(string $query = '', array $conditions = []) :mixed;

    /**
     * Aggregate
     * --------------------------------------------------------------------------------------------------.
     * @param string $type
     * @param string|null $fields
     * @param array $conditions
     * @return mixed
     */
    public function aggregate(string $type, ?string $fields = 'id', array $conditions = []);

    /**
     * Count Records
     * --------------------------------------------------------------------------------------------------.
     * @param array $conditions
     * @param string|null $fields
     * @return int
     */
    public function countRecords(array $conditions = [], ?string $fields = 'id') : int;

    /**
     * Returns a single table row as an object
     * --------------------------------------------------------------------------------------------------.
     * @param array $selectors = []
     * @param array $conditions = []
     * @return null|object
     */
    public function get(array $selectors = [], array $conditions = []) : ?Object;

    /**
     * Get table columns.
     *
     * @param array $options
     * @return object
     */
    public function showColumns(array $options) : object;
}
