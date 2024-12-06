<?php

declare(strict_types=1);
class ParameterDependecyResolver implements DependenciesResolverInterface
{
    public function __construct(private ReflectionNamedType $type, private ReflectionParameter $parameter, private mixed $args)
    {
    }

    public function resolve(int $key): mixed
    {
        $type = $this->type;
        $namedType = $type->getName();
        $optional = $this->parameter->isOptional();
        $promoted = $this->parameter->isPromoted();
        $null = $this->parameter->allowsNull();
        $default = $this->parameter->isDefaultValueAvailable();
        $name = $this->parameter->getName();
        $class = $this->parameter->getDeclaringClass()->getName();
        $method = $this->parameter->getDeclaringFunction();
        $position = $this->parameter->getPosition();
        $variatic = $this->parameter->isVariadic();
        $attr = $this->parameter->getAttributes();

        if (empty($this->args)) {
            return false;
        }
        if (is_array($this->args)) {
            if (array_key_exists($name, $this->args)) {
                return $this->args[$name];
            }
            if (isset($this->args[$position])) {
                return $this->args[$position];
            }
        }
        return false;
    }
}