<?php

declare(strict_types=1);
final class ViewEnvironment
{
    public function getLayout() : string
    {
        return '';
    }

    public function validate(string $templatePath) : string|bool
    {
        return '';
    }
}
