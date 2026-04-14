<?php

declare(strict_types=1);
abstract class AbstractQueryBuilder
{
    protected ?SqlComponent $queryComponent = null;
    protected array $executedComponents = [];
    protected ?string $methodEntry = null;
    protected bool $isBulkQuery = false;
    private int $componentCounter = 0;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getParameters(): array
    {
        return $this->getQueryComponent()->getParameters();
    }

    public function getTableAlias(): array
    {
        return $this->getQueryComponent()->getTableAlias();
    }

    public function getLogicalToPhysicalMap(): array
    {
        return $this->getQueryComponent()->getLogicalToPhysicalMap();
    }

    public function getBindArray(): array
    {
        return $this->getQueryComponent()->getBindArr();
    }

    public function debugSql(): SqlDebugInfo
    {
        return $this->getQueryComponent()->debugSql();
    }

    public function getAliasCheck(): array
    {
        return $this->getQueryComponent()->getAliasCheck();
    }

    public function reset(): void
    {
        $this->getQueryComponent()->resetState();
    }

    public function getTables(): array
    {
        return $this->getQueryComponent()->getTables();
    }

    public function getHydrationData(): array
    {
        return [
            'parameters' => $this->getParameters(),
            'tableAlias' => $this->getTableAlias(),
            'bindArray' => $this->getBindArray(),
            'aliasCheck' => $this->getAliasCheck(),
            'logicalToPhysicalMap' => $this->getQueryComponent()->getLogicalToPhysicalMap(),
            'tables' => $this->getQueryComponent()->getTables(),
        ];
    }

    public function getStatement(): ?SqlStatement
    {
        return $this->getQueryComponent()->getStatement();
    }

    public function hasQuery(): bool
    {
        $comp = $this->getQueryComponent();
        if (!isset($comp)) {
            return false;
        }
        if ($this->shouldSkip()) {
            return false;
        }

        $sql = $this->getQueryComponent()->getQuery();
        return !empty($sql);
    }

    public function getQuery(): string
    {
        return $this->getQueryComponent()->getQuery() ?: '';
    }

    public function setQuery(string $sql): SqlComponent
    {
        return $this->getQueryComponent()->setQuery($sql);
    }

    /**
     * @return null|SqlComponent
     */
    public function getQueryComponent(): ?SqlComponent
    {
        if ($this->queryComponent !== null) {
            return $this->queryComponent;
        }

        $this->queryComponent = $this->findRootComponent();
        return $this->queryComponent;
    }

    public function getComponentByType(string $type): ?SqlComponent
    {
        foreach ($this->executedComponents as $component) {
            $componentType = get_class($component);
            if (str_contains($componentType, $type)) {
                return $component;
            }
        }
        return null;
    }

    public function getAllComponents(): array
    {
        return $this->executedComponents;
    }

    protected function registerComponent(SqlComponent $component, string $method): void
    {
        $uniqueKey = $method . '_' . $this->componentCounter++;
        $this->executedComponents[$uniqueKey] = $component;
        $this->queryComponent = null;

        if ($this->methodEntry === null) {
            $this->methodEntry = $uniqueKey;
        }
    }

    // private function findRootComponent(): ?SqlComponent
    // {
    //     if (empty($this->executedComponents)) {
    //         return null;
    //     }

    //     // 1. If we have an explicit entry point, use it.
    //     if ($this->methodEntry !== null && isset($this->executedComponents[$this->methodEntry])) {
    //         return $this->executedComponents[$this->methodEntry];
    //     }

    //     // 2. Fallback: Find the FIRST component with no parent (The true root)
    //     $roots = array_filter($this->executedComponents, function (SqlComponent $component) {
    //         return $component->getParent() === null;
    //     });

    //     // Use reset() to get the FIRST root found, not end() which gets the latest
    //     return !empty($roots) ? reset($roots) : end($this->executedComponents);
    // }
    private function findRootComponent(): ?SqlComponent
    {
        if (empty($this->executedComponents)) {
            return null;
        }

        $roots = array_filter($this->executedComponents, function (SqlComponent $component) {
            return $component->getParent() === null;
        });

        if (empty($roots)) {
            return end($this->executedComponents);
        }
        return end($roots);
    }

    private function shouldSkip(): bool
    {
        $component = $this->getQueryComponent();
        if (!$component) {
            return false;
        }

        $state = $component->getState();
        if ($state->isUpdate && !$state->hasSetContent) {
            return true;
        }
        return false;
    }
}