<?php

declare(strict_types=1);

abstract class AbstractHtmlSectionManager implements HtmlSectionManagerInterface
{
    /** @var array<string, HtmlSectionInterface> */
    protected array $sections = [];

    protected array $pagination = [];
    protected mixed $id;

    public function registerSection(HtmlSectionInterface $section): void
    {
        $this->sections[$section->getKey()] = $section;
    }

    public function getSection(string|int $key): ?HtmlSectionInterface
    {
        return $this->sections[$key] ?? null;
    }

    public function reset(): void
    {
        $this->sections = [];
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * @param array $pagination
     *
     * @return AbstractHtmlSectionManager
     */
    public function setPagination(array $pagination): AbstractHtmlSectionManager
    {
        $this->pagination = $pagination;

        return $this;
    }
}