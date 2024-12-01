<?php

declare(strict_types=1);

class Form extends FormElements
{
    private string $name;
    private string $action;
    private string $target;
    private string $method;
    private string $autocomplete;
    private bool $enctype;
    private string $rel;
    private string $acceptCharset;
    private string $autocapitalize;
    private string $id;
    private array $class;
    private bool $novalidate;

    public function __construct(Token $token)
    {
        parent::__construct($token);
    }

    public function makeForm(): string
    {
        $results = [];
        foreach ($this->children as $child) {
            $results[] = $child->makeForm();
        }
        return $this->begin() . implode(' ', $results) . $this->end();
    }

    private function begin() : string
    {
        $formBegin = '<form';
        foreach ($this as $prop => $value) {
            $formBegin .= match (true) {
                in_array(gettype($value), ['string', 'bool', 'boolean']) => ' ' . $this->property($prop, $value),
                is_array($value) => ' ' . $this->property($prop, implode(' ', $value)),
                default => ''
            };
        }
        // $formBegin .= ' csrfToken=' . $this->token->create(8, $this->name ?? '');
        return htmlspecialchars_decode($formBegin . '>');
    }

    private function end() : string
    {
        return '</form>';
    }

    private function property(string $prop, string|bool $value) : string
    {
        return match ($prop) {
            'action' => $prop . '=/' . $value ,
            'novalidate' => 'novalidate',
            'acceptCharset' => 'accept-charset=' . $value,
            'enctype' => $prop . "='multipart/form-data'",
            'class' => 'class="' . $value . '"',
            default => $prop . '=' . $value ,
        };
    }
}
