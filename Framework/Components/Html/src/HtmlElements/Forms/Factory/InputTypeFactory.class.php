<?php

declare(strict_types=1);

final readonly class InputTypeFactory
{
    public static function create(string $type) : AbstractInput
    {
        return match (strtolower($type)) {
            'text' => new TextType(),
            'radio' => new RadioType(),
            'hidden' => new HiddenType(),
            'email' => new EmailType(),
            'password' => new PasswordType(),
            'checkbox' => new CheckBoxType(),
            'submit' => new SubmitType()
        };
    }
}