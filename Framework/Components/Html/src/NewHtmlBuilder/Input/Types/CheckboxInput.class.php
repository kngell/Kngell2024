<?php

declare(strict_types=1);

class CheckboxInput extends Input
{
    public function __construct()
    {
        parent::__construct('checkbox');
    }

    public function checked(bool $checked = true): self
    {
        $this->attributes['checked'] = $checked;
        return $this;
    }
}