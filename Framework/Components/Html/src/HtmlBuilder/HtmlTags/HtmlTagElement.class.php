<?php

declare(strict_types=1);

class HtmlTagElement extends AbstractHtmlComponent
{
    private string $tag;
    private string $content;

    public function __construct(string $tag)
    {
        $this->tag = $tag;
    }

    public function generate(): string
    {
        $tag = '<' . $this->tag;
        foreach ($this as $key => $value) {
            if (in_array($key, ['content', 'tag', 'formErrors', 'formValues']) || is_object($value)) {
                continue;
            }
            if (in_array(gettype($value), ['string', 'array'])) {
                $tag .= $this->getTagAttribute($key, $value);
            }
        }
        $tag .= '>';
        if (isset($this->content)) {
            $tag .= $this->content;
        }
        return $tag . '</' . $this->tag . '>';
    }

    /**
     * @param string $content
     * @return HtmlTagElement
     */
    public function content(string $content) : self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $accesskey
     * @return HtmlTagElement
     */
    public function accesskey(string $accesskey): self
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    /**
     * @param array $class
     * @return HtmlTagElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $contenteditable
     * @return HtmlTagElement
     */
    public function contenteditable(string $contenteditable): self
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    /**
     * @param string $data
     * @return HtmlTagElement
     */
    public function data(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $dir
     * @return HtmlTagElement
     */
    public function dir(string $dir): self
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param string $draggable
     * @return HtmlTagElement
     */
    public function draggable(string $draggable): self
    {
        $this->draggable = $draggable;
        return $this;
    }

    /**
     * @param string $enterkeyhint
     * @return HtmlTagElement
     */
    public function enterkeyhint(string $enterkeyhint): self
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    /**
     * @param string $hidden
     * @return HtmlTagElement
     */
    public function hidden(string $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @param string $id
     * @return HtmlTagElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $inert
     * @return HtmlTagElement
     */
    public function inert(string $inert): self
    {
        $this->inert = $inert;
        return $this;
    }

    /**
     * @param string $inputmode
     * @return HtmlTagElement
     */
    public function inputmode(string $inputmode): self
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    /**
     * @param string $lang
     * @return HtmlTagElement
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param string $popover
     * @return HtmlTagElement
     */
    public function popover(string $popover): self
    {
        $this->popover = $popover;
        return $this;
    }

    /**
     * @param string $spellcheck
     * @return HtmlTagElement
     */
    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    /**
     * @param array $style
     * @return HtmlTagElement
     */
    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @param string $tabindex
     * @return HtmlTagElement
     */
    public function tabindex(string $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    /**
     * @param string $title
     * @return HtmlTagElement
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $translate
     * @return HtmlTagElement
     */
    public function translate(string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }
}