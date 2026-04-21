<?php

declare(strict_types=1);

class HtmlBlockElement extends AbstractHtmlComponent
{
    public function __construct(string $htmlBlock = '')
    {
        $this->htmlBlock = $htmlBlock;
    }

    public function generate(): string
    {
        return $this->htmlBlock ?? '';
    }

    /**
     * Load HTML from a file path.
     */
    public function get(string $htmlBlockPath): static
    {
        $content = file_get_contents($htmlBlockPath);
        if ($content !== false) {
            $this->htmlBlock = $content;
        }
        return $this;
    }

    public function blockContent(string $htmlContent): static
    {
        $this->htmlBlock = $htmlContent;
        return $this;
    }
}