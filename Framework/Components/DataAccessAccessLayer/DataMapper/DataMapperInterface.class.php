<?php

declare(strict_types=1);

interface DataMapperInterface
{
    // public function setCredentials(array $credentials) : self;

    /**
     * --------------------------------------------------------------------------------------------------
     * Prepare the query string.
     * @param string $sql
     * @return self
     */
    public function prepare(string $sql) : self;

    /**
     * Bind params.
     * -------------------------------------------------------------------------------------------------.
     * @param mixed $param
     * @param mixed $value
     * @param [type] $type
     * @return void
     * @throws DataMapperExceptions
     */
    public function bind(mixed $param, mixed $value, $type = null);

    /**
     * --------------------------------------------------------------------------------------------------
     * combinaition method wich combines bind type and values.
     *@param array $fields
     *@param bool $isSearch
     *@return bool|self
     */
    public function bindParameters(array $fields = [], bool $isSearch = false) : bool|self;

    /**
     * --------------------------------------------------------------------------------------------------
     * Return number of rows.
     * @return int
     */
    public function numrow(): int;

    /**
     * --------------------------------------------------------------------------------------------------
     * Execute prepare statement.
     * @return void
     */
    public function execute(): mixed;

    /**
     * --------------------------------------------------------------------------------------------------
     * Return sigle object result.
     *@return object
     */
    public function result(): Object;

    /**
     * --------------------------------------------------------------------------------------------------
     * Return all.
     * @param array $options
     * @return self
     */
    public function results(array $options) : self;

    /**
     * --------------------------------------------------------------------------------------------------
     * Get las insert ID.
     * @return int
     * @throws throwable
     */
    public function getLasID(): int;

    public function count() : int;
}
