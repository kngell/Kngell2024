<?php

declare(strict_types=1);

class BuiltInDependencyResolver implements DependenciesResolverInterface
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
        if (empty($args)) {
            return match (true) {
                $this->parameter->allowsNull() => null,
                ($default || $optional) && ! array_key_exists($name, $args) => $this->parameter->getDefaultValue(),
                $namedType === 'string' => '',
                default => throw new DependencyHasNoValueException('Could not resolve class dependency ' . $this->parameter->name)
            };
        }
        if (isset($args[$position]) && $args[$position] instanceof Closure) {
            return $args[$position]->__invoke();
        }
        if (is_array($args)) {
            $parametersTypes = $this->getClassConstructorParametersTypes();
            if (array_key_exists($name, $args)) {
                if (gettype($args[$name]) === $parametersTypes[$name]) {
                    return $args[$name];
                }
            } else {
                if (count($parametersTypes) === 1 && $position === 0) {
                    return $args;
                } else {
                    return isset($args[$position]) ? $args[$position] : false;
                }
            }
        } else {
            return $args;
        }
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
