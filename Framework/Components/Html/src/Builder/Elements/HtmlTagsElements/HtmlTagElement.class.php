<?php

declare(strict_types=1);

class HtmlTagElement extends AbstractHtmlComponent
{
    public function __construct(string $tag)
    {
        $this->tag = $tag;
    }

    public function generate(): string
    {
        $tag = $this->getTagAttributes(get_object_vars($this), $this->tag);

        if (isset($this->content)) {
            $tag .= $this->content;
        }

        if (!in_array(strtolower($this->tag), self::VOID_TAGS, true)) {
            $tag .= '</' . $this->tag . '>';
        }

        return $tag;
    }
}