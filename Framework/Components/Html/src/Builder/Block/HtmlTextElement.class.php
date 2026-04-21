<?php

declare(strict_types=1);

class HtmlTextElement extends AbstractHtmlComponent
{
    public function __construct(string $text = '')
    {
        $this->text = $text;
    }

    public function generate(): string
    {
        return $this->text ?? '';
    }
}