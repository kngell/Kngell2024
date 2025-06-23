<?php

declare(strict_types=1);
class ParameterDependecyResolver implements DependenciesResolverInterface
{
    public function __construct(private ReflectionNamedType $type, private ReflectionParameter $parameter)
    {
    }

    public function resolve(int $key, mixed $args): mixed
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
        $this->parameter->isVariadic();
        if ($default && empty($args) && $namedType !== 'object') {
            return $this->parameter->getDefaultValue();
        }
        if (empty($args)) {
            return false;
        }
        if (is_array($args)) {
            if (array_key_exists($name, $args)) {
                return $args[$name];
            }
            if (isset($args[$position])) {
                return $args[$position];
            }
        }
        return false;
    }
}