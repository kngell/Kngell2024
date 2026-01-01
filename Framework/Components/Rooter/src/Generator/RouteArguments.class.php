<?php

declare(strict_types=1);
class RouteArguments
{
    private string $parameterName;
    private ReflectionNamedType $reflectionType;
    private bool $nullable;
    private bool $optional;
    private bool $isDefaultValue;
    private mixed $defaultValue;
    private array $aliases = [];

    public function __construct(ReflectionParameter $parameter, private ParameterAliasRegistry $aliasRegistry)
    {
        $this->parameterName = $parameter->getName();
        $this->reflectionType = $parameter->getType();
        $this->nullable = $parameter->allowsNull();
        $this->optional = $parameter->isOptional();
        $this->defaultValue($parameter);
        $this->extractAliases($parameter);
        $this->aliases = $this->aliasRegistry->getAliasesFor($this->parameterName);
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * @return ReflectionNamedType
     */
    public function getReflectionType(): ReflectionNamedType
    {
        return $this->reflectionType;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isDefaultValue(): bool
    {
        return $this->isDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    private function extractAliases(ReflectionParameter $parameter): void
    {
        $attributes = $parameter->getAttributes(Alias::class);
        if (!empty($attributes)) {
            $aliasAttribute = $attributes[0]->newInstance();
            $this->aliases = array_merge($this->aliases, $aliasAttribute->names);
        }
    }

    private function defaultValue(ReflectionParameter $parameter): void
    {
        $this->isDefaultValue = $parameter->isDefaultValueAvailable();
        if ($this->isDefaultValue) {
            $this->defaultValue = $parameter->getDefaultValue();
        }
    }
}