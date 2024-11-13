<?php

declare(strict_types=1);

interface QueryBuilderInterface
{
    /**
     * --------------------------------------------------------------------------------------------------
     * Insert/Create a query.
     * @return string
     */
    public function insert() : string;

    /**
     * --------------------------------------------------------------------------------------------------
     * Select Query.
     *@return string
     */
    public function select() : string;

    /**
     * --------------------------------------------------------------------------------------------------
     * update query.
     * @return string
     */
    public function update() : string;

    /**
     * --------------------------------------------------------------------------------------------------
     * Delete query.
     *@return string
     */
    public function delete() : string;

    /**
     * --------------------------------------------------------------------------------------------------
     * Search query.
     *@return string|bool
     */
    public function search() : string|bool;

    /**
     * --------------------------------------------------------------------------------------------------
     * custom query.
     * @return string
     */
    public function customQuery(): string;

    /**
     * Get Columns from database.
     * --------------------------------------------------------------------------------------------------.
     * @return string
     */
    public function showColumn() : string;
}
