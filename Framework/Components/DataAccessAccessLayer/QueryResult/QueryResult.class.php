<?php

declare(strict_types=1);

class QueryResult
{
    private mixed $results;
    private int $rowCount;
    private PDOStatement $_query;

    public function __construct(private DataMapperInterface $mapper, private array $options = [], private string|null $method = null)
    {
        $this->_query = $mapper->getQueryStatement();
        $this->processResults();
        $this->rowCount();
    }

    public function getQueryResult() : bool
    {
        return $this->mapper->getQueryResult();
    }

    public function getResultSet() : self
    {
        return $this->processResults();
        return $this;
    }

    public function getLastInsertId(string|null $name = null) : string|false
    {
        return $this->mapper->getConnexion()->open()->lastInsertId($name);
    }

    /**
     * Get the value of _results.
     *
     * @return mixed
     */
    public function getResults(): mixed
    {
        return $this->results;
    }

    /**
     * Get the value of rowCount.
     *
     * @return int
     */
    public function getNumRows(): int
    {
        return $this->rowCount;
    }

    private function getSingleResult(string $option = '') : mixed
    {
        return match ($option) {
            'object' => $this->_query->fetch(PDO::FETCH_OBJ),
            default => $this->_query->fetch(PDO::FETCH_ASSOC),
        };
    }

    private function processResults() : self
    {
        if ($this->_query) {
            $this->results = match ($this->method) {
                'findByID' => $this->getSingleResult(),
                default => match ($this->method) {
                    'findAll','read','showColumns' => $this->readingFromDatabaseResults($this->options),
                    'create','update','delete' => $this->rowCount(),
                    default => $this->readingFromDatabaseResults()
                },
            };
            return $this;
        }
    }

    private function rowCount() : void
    {
        $this->rowCount = $this->_query->rowCount();
    }

    private function readingFromDatabaseResults(array $options = []) : mixed
    {
        $results_type = $this->returType($options);
        $this->fetchMode($results_type, $options);
        $check = array_key_exists('return_type', $options) ? $options['return_type'] : 'all';
        return match ($check) {
            'count' => $this->_query->rowCount(),
            'single' => $this->_query->fetch(),
            'first' => ArrayUtils::first($this->_query->fetchAll()),
            default => $this->_query->fetchAll()
        };
    }

    private function fetchMode(int $type, array $options) : void
    {
        $className = isset($options['class']) ? $options['class'] : null;
        $contructorArgs = isset($options['constructorArgs']) ? $options['constructorArgs'] : null;
        if ($className != null) {
            if ($contructorArgs != null) {
                $this->_query->setFetchMode($type, $className, $contructorArgs);
            } else {
                $this->_query->setFetchMode($type, $className);
            }
        } else {
            $this->_query->setFetchMode($type);
        }
    }

    private function returType(array $options) : int
    {
        if (array_key_exists('return_type', $options)) {
            return match ($options['return_type']) {
                'object' => PDO::FETCH_OBJ,
                'class' => PDO::FETCH_CLASS,
                default => PDO::FETCH_ASSOC
            };
        }
        return PDO::FETCH_ASSOC;
    }
}
