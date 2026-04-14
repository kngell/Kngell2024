<?php

declare(strict_types=1);

class DefaultModelFactory implements ModelFactoryInterface
{
    public function __construct(private ModelUtilityInterface $utils, private Model $md)
    {
    }

    public function create(string $type): ModelStrategyInterface
    {
        return match($type) {
            // Query strategies
            'all' => new ModelQueryAll($this->utils),
            'one' => new ModelQueryOne($this->utils),
            'find' => new ModelQueryFind($this->utils),
            'first' => new ModelQueryFirst($this->utils),
            'last' => new ModelQueryLast($this->utils),
            'page' => new ModelQueryPage($this->utils),
            'ids' => new ModelQueryIds($this->utils),
            'get' => new ModelQueryGet($this->utils),
            'count' => new ModelQueryCount($this->utils),

            // Operation strategies
            'insert' => new ModelOperationInsert($this->utils, $this->md),
            'update' => new ModelOperationUpdate($this->utils, $this->md),
            'delete' => new ModelOperationDelete($this->utils, $this->md),

            default => throw new InvalidArgumentException("Strategy type '$type' not supported")
        };
    }

    public function supports(string $type): bool
    {
        $supported = [
            'all', 'one', 'find', 'first', 'last', 'page', 'get',
            'insert', 'update', 'delete', 'count', 'ids',
        ];

        return in_array($type, $supported);
    }
}
