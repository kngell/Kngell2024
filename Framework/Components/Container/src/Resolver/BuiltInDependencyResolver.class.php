<?php

declare(strict_types=1);

class BuiltInDependencyResolver implements DependenciesResolverInterface
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
            return match (true) {
                $this->parameter->allowsNull() => null,
                ($default || $optional) && ! array_key_exists($name, $this->args) => $this->parameter->getDefaultValue(),
                default => throw new DependencyHasNoValueException('Could not resolve class dependency ' . $this->parameter->name)
            };
        }
        if ($this->args instanceof Closure) {
            return $this->args->__invoke();
        }
        if (is_array($this->args)) {
            $parametersTypes = $this->getClassConstructorParametersTypes();
            if (array_key_exists($name, $this->args)) {
                if (gettype($this->args[$name]) === $parametersTypes[$name]) {
                    return $this->args[$name];
                }
            } else {
                if (count($parametersTypes) === 1 && $position === 0) {
                    return $this->args;
                } else {
                    return isset($this->args[$position]) ? $this->args[$position] : false;
                }
            }
        } else {
            return $this->args;
        }

        // return match (true) {
        //     $this->parameter->isDefaultValueAvailable() && empty($this->args) => $this->parameter->getDefaultValue(),
        //     is_array($this->args) && array_key_exists($this->parameter->name, $this->args) => $this->args[$this->parameter->name],
        //     ! ArrayUtils::isAssoc($this->args) && array_key_exists($key, $this->args) => $this->args[$key],
        //     $this->args instanceof Closure => $this->args->__invoke(),
        //     ! empty($this->args) => $this->args,
        //     default => false
        // };
    }

    private function getClassConstructorParametersTypes() : array
    {
        $parameters = $this->parameter->getDeclaringClass()->getConstructor()->getParameters();
        $names = [];
        foreach ($parameters as $parameter) {
            $names[$parameter->getName()] = $this->type->getName();
        }
        return $names;
    }
}