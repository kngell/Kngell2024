<?php

declare(strict_types=1);
class RouteArgumentBuilder
{
    private RouteArgumentType $type;
    private PathVariableAttr|RequestBody|HeaderParam|QueryParam|null $attribute = null;
    private string $parameterName;
    private ReflectionNamedType $reflectionType;
    private bool $isNullable = false;
    private mixed $defaultValue = null;

    /**
     * @param RouteArgumentType $type
     * @return self
     */
    public function withType(RouteArgumentType $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param HeaderParam|PathVariableAttribute|QueryParam|RequestBody|null $attribute
     * @return self
     */
    public function withAttribute(PathVariableAttr|RequestBody|HeaderParam|QueryParam|null $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param string $parameterName
     * @return self
     */
    public function withParameterName(string $parameterName): self
    {
        $this->parameterName = $parameterName;
        return $this;
    }

    /**
     * @param ReflectionNamedType $reflectionType
     * @return self
     */
    public function withReflectionType(ReflectionNamedType $reflectionType): self
    {
        $this->reflectionType = $reflectionType;
        return $this;
    }

    /**
     * @param bool $isNullable
     * @return self
     */
    public function withNullable(bool $isNullable): self
    {
        $this->isNullable = $isNullable;
        return $this;
    }

    /**
     * @param mixed $defaultValue
     * @return self
     */
    public function withDefaultValue(mixed $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function build() : RouteArgument
    {
        return new RouteArgument(
            $this->type,
            $this->attribute,
            $this->parameterName,
            $this->reflectionType,
            $this->isNullable,
            $this->defaultValue
        );
    }
}