<?php

declare(strict_types=1);
readonly class PathElement
{
    private PathElementType $type;
    private string $value;

    /**
     * @param PathElementType $type
     * @param string $value
     */
    public function __construct(PathElementType $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return PathElementType
     */
    public function getType(): PathElementType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}