<?php

declare(strict_types=1);
interface HtmlSectionManagerInterface
{
    public function registerSection(HtmlSectionInterface $section): void;

    public function getSections(array $context = []): array;

    public function getSection(string $key): ?HtmlSectionInterface;

    public function reset(): void;
}