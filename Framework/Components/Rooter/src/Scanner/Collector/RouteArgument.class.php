<?php

declare(strict_types=1);
readonly class RouteArgument
{
    private RouteArgumentType $type;
    private string|null $attribute; //PathVariableAttr|RequestBody|HeaderParam|QueryParam
    private string $parameterName;
    private ReflectionNamedType $reflectionType;
    private bool $nullable;
    private mixed $defaultValue;

    /**
     * @param RouteArgumentType $type
     * @param string|null $attribute
     * @param string $parameterName
     * @param ReflectionNamedType $reflectionType
     * @param bool $nullable
     * @param mixed $defaultValue
     */
    public function __construct(
        RouteArgumentType $type,
        string|null $attribute,
        string $parameterName,
        ReflectionNamedType $reflectionType,
        bool $nullable,
        mixed $defaultValue
    ) {
        $this->type = $type;
        $this->attribute = $attribute;
        $this->parameterName = $parameterName;
        $this->reflectionType = $reflectionType;
        $this->nullable = $nullable;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return RouteArgumentType
     */
    public function getType(): RouteArgumentType
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getAttribute(): string|null
    {
        return $this->attribute;
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
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}
