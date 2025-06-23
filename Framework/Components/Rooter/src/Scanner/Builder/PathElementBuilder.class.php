<?php

declare(strict_types=1);
class PathElementBuilder
{
    private PathElementType $type;
    private string $value;

    public function withType(PathElementType $type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function withValue(string $value) : self
    {
        $this->value = $value;
        return $this;
    }

    public function build() :  PathElement
    {
        return new PathElement($this->type, $this->value);
    }
}