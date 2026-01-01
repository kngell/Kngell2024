<?php

declare(strict_types=1);

interface TranslatorServiceInterface
{
    public function translate(string $key, array $parameters = [], ?string $locale = null): string;

    public function getLocale(): string;

    public function setLocale(string $locale): void;

    public function has(string $key, ?string $locale = null): bool;
}