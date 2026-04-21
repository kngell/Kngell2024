<?php

declare(strict_types=1);

abstract class AbstractHtmlComponent
{
    use HtmlAttributesTrait;
    use AriaAttributesTrait;
    use EventAttributesTrait;
    use FormFieldTrait;
    use TagRendererTrait;

    protected int $level = 0;
    protected ?self $parent = null;
    protected string $tag = '';
    protected null|string|int $content = null;
    protected bool $contentUp = true;
    protected CollectionInterface $children;

    public function __construct()
    {
        $this->children = new Collection();
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
        $this->level = $parent ? $parent->getLevel() + 1 : 0;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function add(self|null ...$htmlelements): static
    {
        return $this;
    }

    public function remove(self $component): static
    {
        return $this;
    }

    public function content(null|string|int $content, bool $contentUp = true): static
    {
        $this->content = $content;
        $this->contentUp = $contentUp;
        return $this;
    }

    public function getContent(): null|string|int
    {
        return $this->content;
    }

    public function htmlBlock(?string $htmlBlock = null): HtmlBlockElement
    {
        return new HtmlBlockElement($htmlBlock ?? '');
    }

    public function text(?string $text = null): HtmlTextElement
    {
        return new HtmlTextElement($text ?? '');
    }

    abstract public function generate(): string;
}