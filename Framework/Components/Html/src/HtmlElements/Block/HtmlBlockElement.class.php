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
        if (isset($this->htmlBlock)) {
            return $this->htmlBlock;
        }
        return '';
    }

    /**
     * @param string $htmlBlock
     *
     * @return HtmlBlockElement
     */
    public function get(string $htmlBlockPth): self
    {
        $this->htmlBlock = file_get_contents($htmlBlockPth);
        return $this;
    }

    public function blockContent(string $htmlContent): self
    {
        $this->htmlBlock = $htmlContent;
        return $this;
    }
}