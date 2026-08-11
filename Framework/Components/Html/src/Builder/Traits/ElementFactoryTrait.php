<?php

declare(strict_types=1);

trait ElementFactoryTrait
{
    public function input(string $type): AbstractInput
    {
        $inputType = ucfirst(strtolower($type)) . 'Type';
        try {
            $input = new $inputType();
            if ($type === 'tel') {
                $input->withInternationalFormat()
                ->withPhoneValidation();
            }
            return $input;
        } catch (Throwable $th) {
            throw new FormElementNotFound($inputType);
        }
    }

    public function label(?string $message = null): LabelElement
    {
        return new LabelElement($message);
    }

    public function select(): SelectElement
    {
        return new SelectElement();
    }

    public function option(string $key = '', mixed $value = null): SelectOption
    {
        return new SelectOption($key, $value);
    }

    public function button(string $type = ''): ButtonElement
    {
        return new ButtonElement($type);
    }
}