<?php

declare(strict_types=1);

abstract class AbstractHtmlSectionManager implements HtmlSectionManagerInterface
{
    /** @var array<string, HtmlSectionInterface> */
    protected array $sections = [];

    public function registerSection(HtmlSectionInterface $section): void
    {
        $this->sections[$section->getKey()] = $section;
    }

    public function getSection(string $key): ?HtmlSectionInterface
    {
        return $this->sections[$key] ?? null;
    }

    public function reset(): void
    {
        $this->sections = [];
    }
}