<?php

declare(strict_types=1);

class FileInput extends Input
{
    public function __construct()
    {
        parent::__construct('file');
    }

    public function accept(string $accept): self
    {
        $this->attributes['accept'] = $accept;
        return $this;
    }

    public function multiple(bool $multiple = true): self
    {
        $this->attributes['multiple'] = $multiple;
        return $this;
    }
}