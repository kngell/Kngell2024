<?php

declare(strict_types=1);

use Throwable;

class DataMapper extends AbstractDataMapper implements DataMapperInterface
{
    /**
     * Set Database connection
     * ===================================================================.
     */
    public function __construct(DatabaseConnexionInterface $_con)
    {
        $this->_con = $_con;
    }

    /**
     *@inheritDoc
     */
    public function prepare(string $sql):self
    {
        $this->_query = $this->_con->open()->prepare($sql);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function bind($param, $value, $type = null)
    {
        try {
            $this->_query->bindValue($param, $value, $type === null ? match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            } : $type);
        } catch (\Throwable $ex) {
            throw new DataMapperExceptions($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Bind an Array of Values.
     * ==============================================================.
     * @param array $fields
     * @return void
     */
    public function bindArrayValues(array $fields) : PDOStatement
    {
        if ($this->isArray($fields)) {
            foreach ($fields as $key => $value) {
                $this->_query->bindValue(':' . $key, $value, $this->valueType($value));
            }
        }

        return $this->_query;
    }

    /**
     * Bind Parameters
     * ==============================================================.
     * @inheritDoc
     */
    public function bindParameters(array $fields = [], bool $isSearch = false) : bool|self
    {
        $type = ($isSearch === false) ? $this->bindValues($fields) : $this->biendSearchValues($fields);
        if ($type) {
            return $this;
        }

        return false;
    }

    /**
     * Biend Values
     * ===============================================================.
     * @param array $fields
     * @return PDOStatement
     */
    public function bindValues(array $fields = []) : PDOStatement
    {
        if (!empty($fields)) {
            if (isset($fields['bind_array'])) {
                unset($fields['bind_array']);
            }
            foreach ($fields as $key => $val) {
                if (in_array($key, ['and', 'or'])) {
                    $val = current($val);
                }
                if (is_array($val)) {
                    $this->bindVal($key, $val);
                } else {
                    $val != 'IS NULL' ? $this->bind(":$key", $val) : '';
                }
            }
        }

        return $this->_query;
    }

    /**
     * Bind search values
     * =================================================================.
     * @param array $fields
     */
    public function biendSearchValues(array $fields = [])
    {
        $this->isArray($fields);
        foreach ($fields as $key => $value) {
            $this->_query->bindValue(':' . $key, '%' . $value . '%', $this->valueType($value));
        }

        return $this->_query;
    }

    /**
     * Execute
     * =============================================================.
     *@inheritDoc
     */
    public function execute(): mixed
    {
        if ($this->_query) {
            return $this->_query->execute();
        }
    }

    /**
     *@inheritDoc
     */
    public function result(): Object
    {
        if ($this->_query) {
            return $this->_query->fetch(PDO::FETCH_OBJ);
        }
    }

    /**
     * Results
     * =======================================================================.
     *@inheritDoc
     */
    public function results(array $options = [], string $method = '') : self
    {
        if ($this->_query) {
            $this->_results = match ($method) {
                'read','showColumns' => $this->select_result($options),
                'create','update','delete' => $this->c_u_d_result(),
            };

            return $this;
        }
    }

    /**
     * persist Method
     * =======================================================================.
     * @param string $sql
     * @param array $parameters
     */
    public function persist(string $sql = '', array $parameters = [])
    {
        try {
            $sql = $this->cleanSql($sql);

            return isset($parameters[0]) && $parameters[0] == 'all' ? $this->prepare($sql)->execute() : $this->prepare($sql)->bindParameters($parameters)->execute();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * Build Query parametters
     * =======================================================================.
     * @param array $conditions
     * @param array $parameters
     * @return array
     */
    public function buildQueryParameters(array $conditions = [], array $parameters = []): array
    {
        return (!empty($parameters) || !empty($conditions)) ? array_merge($conditions, $parameters) : $parameters;
    }

    /**
     * @inheritDoc
     */
    public function column()
    {
        if ($this->_query) {
            return $this->_query->fetchColumn();
        }
    }

    public function cleanSql(string $sql)
    {
        $sqlArr = explode('&', $sql);
        if (isset($sqlArr) & count($sqlArr) > 1) {
            $this->bind_arr = unserialize($sqlArr[1]);
        }

        return $sqlArr[0];
    }

    /**
     * Bind usual values.
     * ================================================.
     * @param string $key
     * @param array $val
     * @return void
     */
    private function bindVal(string $key, array $val) : void
    {
        switch (true) {
            case isset($val['operator']) && in_array($val['operator'], ['=', '!=', '>', '<', '>=', '<=']):
                if (isset($val['value']) && is_array($val['value'])) {
                    $this->bind(":$key", $val['value'][1] . '.' . $val['value'][0]);
                } else {
                    $this->bind(":$key", $val['value']);
                }
                break;
            case isset($val['operator']) && in_array($val['operator'], ['NOT IN', 'IN']):
                if (!empty($this->bind_arr)) {
                    foreach ($this->bind_arr as $k => $v) {
                        $this->bind(":$k", $v);
                    }
                }
                break;
            default:
                $this->bind(":$key", $val['value']);
                break;
        }
    }
}
