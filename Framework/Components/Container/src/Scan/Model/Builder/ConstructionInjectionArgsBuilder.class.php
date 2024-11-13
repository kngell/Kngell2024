<?php

declare(strict_types=1);
class ConstructionInjectionArgsBuilder
{
    private ReflectionParameter $parameter;
    private ConstructorInjectionArgType $type;
    private string|null $qualifier = null;

    public function withParameter(ReflectionParameter $parameter): self
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function withType(ConstructorInjectionArgType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function withQualifier(?string $qualifier): self
    {
        $this->qualifier = $qualifier;
        return $this;
    }

    public function build() : ConstructorInjectionArg
    {
        return new ConstructorInjectionArg(
            $this->parameter,
            $this->type,
            $this->qualifier
        );
    }
}