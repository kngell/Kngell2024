<?php

declare(strict_types=1);

class DataMapper extends AbstractDataMapper
{
    private bool $queryResult;

    /**
     * @param DatabaseConnectionInterface $_con
     * @return void
     */
    public function __construct(DatabaseConnectionInterface $_con)
    {
        parent::__construct($_con);
    }

    public function persist(string $sql = '', array $parameters = [], bool $isSearch = false) : self
    {
        try {
            return $this->prepare($sql)->bindParameters($parameters, $isSearch)->execute();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * Get the value of queryResult.
     *
     * @return bool
     */
    public function getQueryResult(): bool
    {
        return $this->queryResult;
    }

    public function hasResults() : bool
    {
        if ($this->queryResult === true) {
            return true;
        }
        return false;
    }

    /**
     * @return DataMapper
     * @throws PDOException
     * @throws DataMapperExceptions
     */
    private function execute(): self
    {
        if ($this->_query) {
            $this->queryResult = $this->_query->execute();
        } else {
            throw new DataMapperExceptions('An error occures during query execution');
        }
        return $this;
    }

    /**
     * @param array $fields
     * @return DataMapper
     * @throws DataMapperExceptions
     */
    private function bindSearchValues(array $fields = []) : self
    {
        $this->isArray($fields);
        foreach ($fields as $key => $value) {
            $this->_query->bindValue(':' . $key, '%' . $value . '%', $this->valueType($value));
        }
        return $this;
    }

    /**
     * @param array $fields
     * @return PDOStatement
     * @throws DataMapperExceptions
     */
    private function bindArrayValues(array $fields) : PDOStatement
    {
        if ($this->isArray($fields)) {
            foreach ($fields as $key => $value) {
                $this->_query->bindValue(':' . $key, $value, $this->valueType($value));
            }
        }
        return $this->_query;
    }

    private function bindParameters(array $parameters = [], bool $isSearch = false) : bool|self
    {
        $type = ($isSearch === false) ? $this->bindArrayValues($parameters) : $this->bindSearchValues($parameters);
        if ($type) {
            return $this;
        }
        return false;
    }

    /**
     * @param string $param
     * @param mixed $value
     * @param int|null $type
     * @return DataMapper
     * @throws DataMapperExceptions
     */
    private function bindValues(string $param, mixed $value, int|null $type = null) : self
    {
        try {
            $this->_query->bindValue($param, $value, $type === null ? $this->valueType($value) : $type);
            return $this;
        } catch (Throwable $ex) {
            throw new DataMapperExceptions($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param string $sql
     * @return DataMapper
     */
    private function prepare(string $sql) : self
    {
        $this->_query = $this->_con->open()->prepare($sql);
        return $this;
    }

    private function valueType(mixed $value) : int
    {
        try {
            return match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
        } catch (DataMapperExceptions $ex) {
            throw $ex;
        }
    }

    private function isArray(array $value) : bool
    {
        if (! is_array($value)) {
            throw new DataMapperExceptions('Your argument need to be an array!');
        }
        return true;
    }
}
