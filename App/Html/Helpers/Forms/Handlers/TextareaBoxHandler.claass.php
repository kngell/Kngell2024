<?php

declare(strict_types=1);

class TextareaBoxHandler extends AbstractTexareaHandler
{
    #[Override]
    protected function getTextareaClasses(): array
    {
        return ['input-box__textarea'];
    }
}