<?php

declare(strict_types=1);

class TextareaFieldHandler extends AbstractTexareaHandler
{
    #[Override]
    protected function getTextareaClasses(): array
    {
        return ['input-field__textarea'];
    }
}