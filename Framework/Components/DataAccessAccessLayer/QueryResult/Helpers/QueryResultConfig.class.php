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

    private ?array $constructorArgs = null;
    private string $fetchMode = 'array';
    private ?int $columnIndex = null;

    public function __construct(
        private ?string $className,
        private array $tableAlias,
        private array $tableMap,
    ) {
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
        $mode = self::FETCH_MODE_MAP[$this->fetchMode] ?? PDO::FETCH_ASSOC;

        if ($mode === PDO::FETCH_COLUMN && $this->columnIndex !== null) {
        }
        return $mode;
    }

    public function getColumnIndex(): ?int
    {
        return $this->columnIndex;
    }

    public function processFetchOptions(string|array|null $fetchOptions, string $entityClassName): void
    {
        if ($fetchOptions === null) {
            return;
        }

        if (is_string($fetchOptions)) {
            $this->fetchMode = $fetchOptions;
            if ($fetchOptions === 'class') {
                $this->className = $entityClassName;
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
        if (!array_key_exists($fetchMode, self::FETCH_MODE_MAP)) {
            throw new InvalidArgumentException("Invalid fetch mode: $fetchMode");
        }
        $this->fetchMode = $fetchMode;
        return $this;
    }

    public function setColumnIndex(?int $columnIndex): self
    {
        $this->columnIndex = $columnIndex;
        return $this;
    }

    public function convertLegacyParams(string|array|null $params, ?string $className, string $entity): ?array
    {
        if ($params === null) {
            return null;
        }

        if (is_string($params)) {
            $fetchOptions = ['mode' => $params];
            if ($params === 'class') {
                $fetchOptions['class'] = $className ?? $entity;
            }
            return $fetchOptions;
        }

        if (is_array($params)) {
            return $params;
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * @return array
     */
    public function getTableMap(): array
    {
        return $this->tableMap;
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

        if (isset($fetchOptions['column_index'])) {
            $this->columnIndex = (int) $fetchOptions['column_index'];
        }
    }
}