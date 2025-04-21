<?php

declare(strict_types=1);
class ContainerTypeErrorResolver implements DependenciesResolverInterface
{
    public function __construct(private ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type, private ReflectionParameter $parameter)
    {
    }

    public function resolve(int $key): mixed
    {
        if ($this->type === null) {
            throw new ContainerTypeErrorException("Constructor Parameter '{$this->parameter->getName()}' in the class '{$this->parameter->getDeclaringClass()->getName()}' has no type declaration");
        }
        return $this->type;
    }
}