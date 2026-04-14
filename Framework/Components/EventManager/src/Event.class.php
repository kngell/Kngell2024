<?php

declare(strict_types=1);

abstract class Event implements EventInterface
{
    protected mixed $params = [];
    private ?object $object = null;
    private string $name = '';
    private mixed $results = null;

    public function __construct(
        string $name = '',
        ?object $object = null,
        array $params = [],
    ) {
        $this->object = $object;
        $this->params = $params;
        $this->name = $name !== '' ? $name : static::class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * @param mixed $results
     *
     * @return EventInterface
     */
    public function setResults(mixed $results): self
    {
        $this->results = $results;
        return $this;
    }

    public function addResult(string $key, mixed $value): self
    {
        $this->results[$key] = $value;
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function hasDatabaseChanges(): bool
    {
        if (is_array($this->results)) {
            foreach ($this->results as $result) {
                if ($result instanceof QueryResult && $result->getAffectedRows() > 0) {
                    return true;
                }

                if ($result === true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getParams(): mixed
    {
        return $this->params;
    }
}