<?php

declare(strict_types=1);
final class ViewValidator
{
    private function __construct()
    {
    }

    public static function validate(string $templatePath) : string|bool
    {
        if (! file_exists($templatePath)) {
            return false;
        }
        return $templatePath;
    }
}