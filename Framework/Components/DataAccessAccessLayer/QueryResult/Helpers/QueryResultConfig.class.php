<?php

declare(strict_types=1);

class QueryResultConfig
{
    /** @var array<string, int> Map of return types to PDO fetch constants */
    public const FETCH_MODE_MAP = [
        'array' => PDO::FETCH_ASSOC,
        'object' => PDO::FETCH_OBJ,
        'class' => PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
        'column' => PDO::FETCH_COLUMN,
        'key_pair' => PDO::FETCH_KEY_PAIR,
    ];

    private ?string $className = null;
    private ?array $constructorArgs = null;
    private string $fetchMode = 'array';

    public function __construct(private Entity $entity, private array $tableMap)
    {
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getConstructorArgs(): ?array
    {
        return $this->constructorArgs;
    }

    public function getFetchMode(): string
    {
        return $this->fetchMode;
    }

    public function getPdoFetchMode(): int
    {
        return self::FETCH_MODE_MAP[$this->fetchMode] ?? PDO::FETCH_ASSOC;
    }

    public function processFetchOptions(string|array|null $fetchOptions, Entity $entity): void
    {
        if ($fetchOptions === null) {
            return;
        }

        if (is_string($fetchOptions)) {
            $this->fetchMode = $fetchOptions;
            if ($fetchOptions === 'class') {
                $this->className = $entity::class;
            }
        } elseif (is_array($fetchOptions)) {
            $this->processArrayFetchOptions($fetchOptions);
        }
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;
        return $this;
    }

    public function setConstructorArgs(?array $constructorArgs): self
    {
        $this->constructorArgs = $constructorArgs;
        return $this;
    }

    public function setFetchMode(string $fetchMode): self
    {
        $this->fetchMode = $fetchMode;
        return $this;
    }

    /**
     * Convert legacy parameters to new fetch options format.
     */
    public function convertLegacyParams(string|array|null $params, ?string $className, Entity $entity): ?array
    {
        if ($params === null) {
            return null;
        }

        if (is_string($params)) {
            $fetchOptions = ['mode' => $params];
            if ($params === 'class') {
                $fetchOptions['class'] = $className ?? $entity::class;
            }
            return $fetchOptions;
        }

        if (is_array($params)) {
            return $params;
        }

        return null;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    private function processArrayFetchOptions(array $fetchOptions): void
    {
        if (isset($fetchOptions['mode'])) {
            $this->fetchMode = $fetchOptions['mode'];
        }

        if (isset($fetchOptions['class'])) {
            $this->className = $fetchOptions['class'];
            $this->constructorArgs = $fetchOptions['constructor_args'] ?? null;
        }
    }
}