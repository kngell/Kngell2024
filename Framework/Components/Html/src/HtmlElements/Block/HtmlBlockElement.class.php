<?php

declare(strict_types=1);

class HtmlBlockElement extends AbstractHtmlComponent
{
    protected string $htmlBlock;

    public function generate(): string
    {
        if (isset($this->htmlBlock)) {
            return $this->htmlBlock;
        }
        return '';
    }

    /**
     * @param string $htmlBlock
     * @return HtmlBlockElement
     */
    public function get(string $htmlBlockPth): self
    {
        $this->htmlBlock = file_get_contents($htmlBlockPth);
        return $this;
    }

    public function content(string $htmlContent) : self
    {
        $this->htmlBlock = $htmlContent;
        return $this;
    }
}