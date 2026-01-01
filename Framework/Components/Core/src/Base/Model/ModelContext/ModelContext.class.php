<?php

declare(strict_types=1);

class ModelContext implements ModelContextInterface
{
    private array $strategies = [];
    private array $strategyGroups = [
        'query' => ['all', 'one', 'find', 'first', 'last', 'page', 'get', 'ids'],
        'operation' => ['save', 'insert', 'update', 'delete'],
        'utility' => ['count', 'exists'],
    ];

    public function __construct(private ModelFactoryInterface $factory)
    {
        $this->initializeStrategies();
    }

    /**
     * @param string $operation
     * @param EntityManagerInterface $em
     * @param Entity $entity
     * @param mixed $params
     *
     * @return QueryResult
     */
    public function execute(string $operation, EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        if (!$this->has($operation)) {
            throw new InvalidArgumentException("Operation '$operation' not supported");
        }

        return $this->strategies[$operation]->execute($em, $entity, $params);
    }

    public function executeGroup(string $group, string $operation, EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        if (!isset($this->strategyGroups[$group])) {
            throw new InvalidArgumentException("Strategy group '$group' not defined");
        }

        if (!in_array($operation, $this->strategyGroups[$group])) {
            throw new InvalidArgumentException("Operation '$operation' not in group '$group'");
        }

        return $this->execute($operation, $em, $entity, $params);
    }

    public function register(string $name, ModelStrategyInterface $strategy, string $group = 'custom'): void
    {
        $this->strategies[$name] = $strategy;
        $this->strategyGroups[$group][] = $name;
    }

    public function registerGroup(string $group, array $strategies): void
    {
        foreach ($strategies as $name => $strategy) {
            $this->register($name, $strategy, $group);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->strategies[$name]);
    }

    public function getGroupOperations(string $group): array
    {
        return $this->strategyGroups[$group] ?? [];
    }

    public function isQueryOperation(string $operation): bool
    {
        return in_array($operation, $this->strategyGroups['query'] ?? []);
    }

    public function isWriteOperation(string $operation): bool
    {
        return in_array($operation, $this->strategyGroups['operation'] ?? []);
    }

    private function initializeStrategies(): void
    {
        foreach ($this->strategyGroups as $group => $operations) {
            foreach ($operations as $operationName) {
                if ($this->factory->supports($operationName)) {
                    $strategyInstance = $this->factory->create($operationName);
                    $this->strategies[$operationName] = $strategyInstance;
                }
            }
        }
    }
}