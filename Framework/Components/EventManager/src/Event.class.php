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
     * @return mixed
     */
    public function getResults(): mixed
    {
        return $this->results;
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

    /**
     * @return mixed
     */
    public function getParams(): mixed
    {
        return $this->params;
    }
}