<?php

declare(strict_types=1);

/**
 * The base Component class declares common operations for both simple and
 * complex objects of a composition.
 */
abstract class MainQuery
{
    /**
     * @var MainQuery|null
     */
    protected $parent;

    protected string $method;
    protected int $level = 0;
    protected MethodList $methodList;
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected array $bind_arr = [];
    protected string $query = '';
    protected string|null $table;
    protected Token $token;
    protected EntityManagerInterface $em;
    protected ?QueryType $queryType;

    /**
     * Optionally, the base Component can declare an interface for setting and
     * accessing a parent of the component in a tree structure. It can also
     * provide some default implementation for these methods.
     */
    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    /**
     * In some cases, it would be beneficial to define the child-management
     * operations right in the base Component class. This way, you won't need to
     * expose any concrete component classes to the client code, even during the
     * object tree assembly. The downside is that these methods will be empty
     * for the leaf-level components.
     */
    public function add(self $component): void
    {
    }

    public function remove(self $component): void
    {
    }

    /**
     * You can provide a method that lets the client code figure out whether a
     * component can bear children.
     */
    public function isComposite(): bool
    {
        return false;
    }

    /**
     * The base Component may implement some default behavior or leave it to
     * concrete classes (by declaring the method containing the behavior as
     * "abstract").
     */
    abstract public function getSql(): array;

    /**
     * Set the value of method.
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of method.
     *
     * @return string
     */
    public function getMethod(): string|null
    {
        if (isset($this->method)) {
            return $this->method;
        }
        return null;
    }

    /**
     * Get the value of tableAlias.
     *
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * Set the value of tableAlias.
     *
     * @param array $tableAlias
     *
     * @return self
     */
    public function setTableAlias(array $tableAlias): self
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * Get the value of aliasCheck.
     *
     * @return array
     */
    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    /**
     * Set the value of aliasCheck.
     *
     * @param array $aliasCheck
     *
     * @return self
     */
    public function setAliasCheck(array $aliasCheck): self
    {
        $this->aliasCheck = $aliasCheck;

        return $this;
    }

    /**
     * Get the value of parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set the value of parameters.
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the value of bind_arr.
     *
     * @return array
     */
    public function getBindArr(): array
    {
        return $this->bind_arr;
    }

    /**
     * Set the value of bind_arr.
     *
     * @param array $bind_arr
     *
     * @return self
     */
    public function setBindArr(array $bind_arr): self
    {
        $this->bind_arr = $bind_arr;

        return $this;
    }

    /**
     * Get the value of query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Set the value of query.
     *
     * @param string $query
     *
     * @return self
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the value of table.
     *
     * @return string|null
     */
    public function getTable(): string|null
    {
        return $this->table;
    }

    /**
     * Set the value of table.
     *
     * @param string|null $table
     *
     * @return self
     */
    public function setTable(string|null $table): self
    {
        $this->table = $table;

        return $this;
    }

    protected function link() : string
    {
        $methodArr = $this->methodList->getMethods();
        $counts = array_count_values($methodArr);
        $stCase = Statement::getFromValue($this->method);
        if (! empty($stCase) && in_array($stCase->value, ['where', 'having']) && $counts[$stCase->value] === 1) {
            $this->methodList->setWhereCondition(true);
            return '';
        }
        $lastKey = array_key_last($methodArr);
        $method = $this->method;
        if ($lastKey === null && empty($method)) {
            return '';
        }
        if ($lastKey === 0) {
            return '';
        }
        if (! Statement::isCondition($method)) {
            return '';
        }
        if (! Statement::isCondition($methodArr[$lastKey - 1])) {
            return '';
        }
        if (str_contains(strtolower($method), 'or')) {
            return ' OR ';
        }
        if (str_contains(strtolower($method), 'on')) {
            return 'ON';
        } else {
            return 'AND ';
        }
    }

    protected function statement(string $method) : string
    {
        if (empty($this->method)) {
            return '';
        }
        // $statement = Statement::getFromValue($method)->name;
        // return strtoupper(str_replace('_', ' ', $statement)) . ' ';
        if (! empty($method) && Statement::exists($method)) {
            $statement = Statement::getFromValue($method)->name;
            if (str_ends_with(strtolower($statement), 'join')) {
                return '';
            }
            return strtoupper(str_replace('_', ' ', $statement)) . ' ';
        }
        return '';
    }
}