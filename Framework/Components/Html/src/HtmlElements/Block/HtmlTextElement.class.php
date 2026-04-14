<?php

declare(strict_types=1);

class HtmlTextElement extends AbstractHtmlComponent
{
    public function __construct(string $htmlText = '')
    {
        $this->text = $htmlText;
    }

    public function generate(): string
    {
        if (isset($this->text)) {
            return $this->text;
        }
        return '';
    }
}