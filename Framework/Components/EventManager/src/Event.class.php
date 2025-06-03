<?php

declare(strict_types=1);

abstract class Event implements EventInterface
{
    protected mixed $params = [];
    private ?object $object = null;
    private string $name = '';
    private mixed $results = null;

    public function __construct(?Object $object = null, string $name = '')
    {
        $this->object = $object;
        if ($name === '') {
            $name = $this::class ?? '';
        }
        $this->name = $name;
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
     * @return EventInterface
     */
    public function setResults(mixed $results): self
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @param null|object $object
     * @return EventInterface
     */
    public function setObject(?object $object): self
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams(): mixed
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @return EventInterface
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}