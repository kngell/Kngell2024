<?php

declare(strict_types=1);
class RouteArguments
{
    // private RouteArgumentType $type;
    private string $parameterName;
    private ReflectionNamedType $reflectionType;
    private bool $nullable;
    private bool $optional;
    private bool $isDefaultValue;
    private mixed $defaultValue;

    /**
     * @param string $parameterName
     * @param ReflectionNamedType $reflectionType
     * @param bool $nullable
     * @param mixed $defaultValue
     */
    public function __construct(ReflectionParameter $parameter)
    {
        // $this->type = RouteArgumentType::REQUEST;
        $this->parameterName = $parameter->getName();
        $this->reflectionType = $parameter->getType();
        $this->nullable = $this->nullable($parameter);
        $this->defaultValue($parameter);
        $this->optional = $this->optional($parameter);
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

    private function optional(ReflectionParameter $parameter) : bool
    {
        return $parameter->isOptional();
    }

    private function nullable(ReflectionParameter $parameter) : bool
    {
        return $parameter->allowsNull();
    }

    private function defaultValue(ReflectionParameter $parameter) : void
    {
        $this->isDefaultValue = $parameter->isDefaultValueAvailable();
        if ($this->isDefaultValue) {
            $this->defaultValue = $parameter->getDefaultValue();
        }
    }
}