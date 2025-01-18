<?php

declare(strict_types=1);

abstract readonly class AbstractTemplateForm implements FormTemplateInterface
{
    protected const array INPUT_BOX_CLASS = ['flex-nowrap', 'mb-3', 'input-box'];
    protected const array INPUT_CLASS = ['form-control', 'input-box__input'];
    protected const array LABEL_CLASS = ['form-check-label', 'input-box__label'];
    protected const array INPUT_CHECKBOX_CLASS = ['form-check-input', 'input-box__checkbox'];
    protected const array CHECKBOX_CLASS = ['form-check'];
    protected const array BUTTON_CLASS = ['btn', 'btn-light'];

    //"^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d\W]{8,}$"; //One letter, one number and at least 8 chars
    //Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character
    protected const string PASSWORD_PATTERN = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$";

    protected function formValues(array|Entity|bool $formValues) : array
    {
        return match (true) {
            $formValues instanceof Entity => $formValues->toOriginalArray(),
            is_bool($formValues) => [],
            default => $formValues
        };
    }
}