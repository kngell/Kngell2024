<?php

declare(strict_types=1);

class ConditionResult
{
    public Entity $entity;
    public array $conditions;

    public function __construct(Entity $entity, array $conditions = [])
    {
        $this->entity = $entity;
        $this->conditions = $conditions;
    }
}