<?php

declare(strict_types=1);

class QueryResult
{
    private mixed $results;
    private int $rowCount;
    private PDOStatement $_query;
    private string $returnType;

    public function __construct(private DataMapperInterface $mapper, private Entity $entity)
    {
        $this->_query = $mapper->getQueryStatement();
    }

    public function getLastInsertId(string|null $name = null) : string|false
    {
        return $this->mapper->getConnexion()->open()->lastInsertId($name);
    }

    public function getResults(string|array|null $params = null, string|null $className = null): self
    {
        list($mode, $className, $constructorArgs) = $this->params($params, $className);
        $mode = $this->returnType($mode);
        $this->fetchMode($mode, $className, $constructorArgs);
        $this->rowCount = $this->_query->rowCount();
        return $this;
    }

    public function count() : mixed
    {
        $this->returnType = 'count';
        return $this->results = $this->readResults();
    }

    public function single() : mixed
    {
        $this->returnType = 'single';
        return $this->results = $this->readResults();
    }

    public function first() : mixed
    {
        $this->returnType = 'first';
        return $this->results = $this->readResults();
    }

    public function all() : mixed
    {
        $this->returnType = 'all';
        return $this->results = $this->readResults();
    }

    public function rowCount() : int
    {
        return $this->rowCount = $this->_query->rowCount();
    }

    public function getQueryResult(): bool
    {
        return $this->mapper->getQueryResult();
    }

    private function params(string|array|null $params = null, string|null $className = null) : array
    {
        return match (true) {
            is_string($params) => $this->stringOptions($params, $className),
            is_array($params) => $this->arrayOptions($params),
            default => ['', null, null],
        };
    }

    private function stringOptions(string $params, string|null $className = null) : array
    {
        if ($params === 'class') {
            $className = $className === null ? $this->entity::class : $className;
        }
        return [$params, $className ?? null, null];
    }

    private function arrayOptions(array $params) : array
    {
        if (array_key_exists('class', $params)) {
            if (array_key_exists('constructorArgs', $params)) {
                $constructorArs = $params['constructorArgs'];
            }
            return [key($params), $params[key($params)], $constructorArs ?? null];
        }
        return ['', null, null];
    }

    private function readResults() : mixed
    {
        return match ($this->returnType) {
            'count' => $this->_query->rowCount(),
            'single' => $this->_query->fetch(),
            'first' => ArrayUtils::first($this->_query->fetchAll()),
            'all' => $this->_query->fetchAll(),
            default => $this->_query->fetchAll()
        };
    }

    private function fetchMode(int $type, string|null $className = null, ?array $constructorAgrs = null) : void
    {
        if ($className != null) {
            if ($constructorAgrs != null) {
                $this->_query->setFetchMode($type, $className, $constructorAgrs);
            } else {
                $this->_query->setFetchMode($type, $className);
            }
        } else {
            $this->_query->setFetchMode($type);
        }
    }

    private function returnType(string|null $type) : int
    {
        return match ($type) {
            'object' => PDO::FETCH_OBJ,
            'class' => PDO::FETCH_CLASS,
            default => PDO::FETCH_ASSOC
        };
    }
}
