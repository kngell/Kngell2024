<?php

declare(strict_types=1);

final class MethodList
{
    /** @var MethodList */
    private static $instance;

    private array $methods = [];
    private bool $whereCondition = false;

    private function __construct()
    {
    }

    /**
     * @return MethodList
     */
    public static function getInstance() : self
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Get the value of methods.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Set the value of methods.
     *
     * @param string $methods
     *
     * @return self
     */
    public function setMethods(string|null $method = null): self
    {
        if (isset($method) && ! empty($method)) {
            $this->methods[] = $method;
        }
        return $this;
    }

    /**
     * Get the value of whereCondition.
     *
     * @return bool
     */
    public function isWhereCondition(): bool
    {
        return $this->whereCondition;
    }

    /**
     * Set the value of whereCondition.
     *
     * @param bool $whereCondition
     *
     * @return self
     */
    public function setWhereCondition(bool $whereCondition): self
    {
        $this->whereCondition = $whereCondition;

        return $this;
    }
}