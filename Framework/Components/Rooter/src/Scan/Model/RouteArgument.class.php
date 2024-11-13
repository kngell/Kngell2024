<?php

declare(strict_types=1);
readonly class RouteArgument
{
    private RouteArgumentType $type;
    private PathVariableAttr|RequestBody|HeaderParam|QueryParam|null $attribute;
    private string $parameterName;
    private ReflectionNamedType $reflectionType;
    private bool $isNullable;
    private mixed $defaultValue;

    /**
     * @param RouteArgumentType $type
     * @param HeaderParam|PathVariableAttribute|QueryParam|RequestBody|null $attribute
     * @param string $parameterName
     * @param ReflectionNamedType $reflectionType
     * @param bool $isNullable
     * @param mixed $defaultValue
     */
    public function __construct(
        RouteArgumentType $type,
        PathVariableAttr|RequestBody|HeaderParam|QueryParam|null $attribute,
        string $parameterName,
        ReflectionNamedType $reflectionType,
        bool $isNullable,
        mixed $defaultValue
    ) {
        $this->type = $type;
        $this->attribute = $attribute;
        $this->parameterName = $parameterName;
        $this->reflectionType = $reflectionType;
        $this->isNullable = $isNullable;
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
     * @return HeaderParam|PathVariableAttribute|QueryParam|RequestBody|null
     */
    public function getAttribute(): PathVariableAttr|RequestBody|HeaderParam|QueryParam|null
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
        return $this->isNullable;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}