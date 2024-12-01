<?php

declare(strict_types=1);
class SelectOption extends FormElement
{
    public function __construct(private string $value, private string $content)
    {
        echo 'hello from select option';
    }

    public function makeForm(): string
    {
        $option = '<option  value=' . $this->value;
        $option .= '>' . $this->content;
        return $option . '</option>';
    }
}
