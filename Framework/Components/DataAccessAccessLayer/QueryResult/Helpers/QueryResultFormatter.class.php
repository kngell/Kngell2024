<?php

declare(strict_types=1);

class QueryResultFormatter
{
    public function __construct(
        private QueryResult $queryResult,
        private QueryResultConfig $config,
    ) {
    }

    public function asArray(): mixed
    {
        $this->queryResult->execute(['mode' => 'array']);

        return match ($this->queryResult->getOperation()) {
            'all' => $this->queryResult->all(),
            'first' => $this->queryResult->first(),
            'single' => $this->queryResult->single(),
            'last' => $this->queryResult->last(),
            default => $this->queryResult->all()
        };
    }

    public function asClass(?string $entityClass = null): mixed
    {
        $this->queryResult->execute(['mode' => 'class', 'class' => $entityClass ?? $this->queryResult->getEntity()::class]);

        return match ($this->queryResult->getOperation()) {
            'all' => $this->queryResult->all(),
            'first' => $this->queryResult->first(),
            'single' => $this->queryResult->single(),
            'last' => $this->queryResult->last(),
            default => $this->queryResult->all()
        };
    }

    public function asColumn(int $columnIndex = 0): array
    {
        $this->queryResult->execute(['mode' => 'column', 'constructor_args' => [$columnIndex]]);
        return $this->queryResult->all();
    }

    public function asKeyPairs(): array
    {
        $this->queryResult->execute(['mode' => 'key_pair']);
        return $this->queryResult->all();
    }

    public function asObject(): mixed
    {
        $this->queryResult->execute(['mode' => 'object']);

        return match ($this->queryResult->getOperation()) {
            'all' => $this->queryResult->all(),
            'first' => $this->queryResult->first(),
            'single' => $this->queryResult->single(),
            'last' => $this->queryResult->last(),
            default => $this->queryResult->all()
        };
    }
}