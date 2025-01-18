<?php

declare(strict_types=1);
class HtmlaElement extends AbstractHtmlComponent
{
    private const string TAG = 'a';
    private string $attributionsrc;
    private string $download;
    private string $href;
    private string $hreflang;
    private string $ping;
    private string $referrerpolicy;
    private string $rel;
    private string $target;
    private string $type;

    public function generate(): string
    {
        $tag = $this->getTagAttributes(get_object_vars($this), self::TAG);
        if (isset($this->content)) {
            $tag .= $this->content;
        }
        return $tag . '</a>';
    }

    /**
     * @param array $custom
     * @return HtmlaElement
     */
    public function custom(array $custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * @param string $attributionsrc
     * @return HtmlaElement
     */
    public function attributionsrc(string $attributionsrc): self
    {
        $this->attributionsrc = $attributionsrc;
        return $this;
    }

    /**
     * @param string $download
     * @return HtmlaElement
     */
    public function download(string $download): self
    {
        $this->download = $download;
        return $this;
    }

    /**
     * @param string $href
     * @return HtmlaElement
     */
    public function aref(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    /**
     * @param string $hreflang
     * @return HtmlaElement
     */
    public function hreflang(string $hreflang): self
    {
        $this->hreflang = $hreflang;
        return $this;
    }

    /**
     * @param string $ping
     * @return HtmlaElement
     */
    public function ping(string $ping): self
    {
        $this->ping = $ping;
        return $this;
    }

    /**
     * @param string $referrerpolicy
     * @return HtmlaElement
     */
    public function referrerpolicy(string $referrerpolicy): self
    {
        $this->referrerpolicy = $referrerpolicy;
        return $this;
    }

    /**
     * @param string $rel
     * @return HtmlaElement
     */
    public function rel(string $rel): self
    {
        $this->rel = $rel;
        return $this;
    }

    /**
     * @param string $target
     * @return HtmlaElement
     */
    public function target(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @param string $type
     * @return HtmlaElement
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $content
     * @return HtmlaElement
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $accesskey
     * @return HtmlaElement
     */
    public function accesskey(string $accesskey): self
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    /**
     * @param array $class
     * @return HtmlaElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $contenteditable
     * @return HtmlaElement
     */
    public function contenteditable(string $contenteditable): self
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    /**
     * @param string $data
     * @return HtmlaElement
     */
    public function data(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $dir
     * @return HtmlaElement
     */
    public function dir(string $dir): self
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param string $draggable
     * @return HtmlaElement
     */
    public function draggable(string $draggable): self
    {
        $this->draggable = $draggable;
        return $this;
    }

    /**
     * @param string $enterkeyhint
     * @return HtmlaElement
     */
    public function enterkeyhint(string $enterkeyhint): self
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    /**
     * @param string $hidden
     * @return HtmlaElement
     */
    public function hidden(string $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @param string $id
     * @return HtmlaElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $inert
     * @return HtmlaElement
     */
    public function inert(string $inert): self
    {
        $this->inert = $inert;
        return $this;
    }

    /**
     * @param string $inputmode
     * @return HtmlaElement
     */
    public function inputmode(string $inputmode): self
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    /**
     * @param string $lang
     * @return HtmlaElement
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param string $popover
     * @return HtmlaElement
     */
    public function popover(string $popover): self
    {
        $this->popover = $popover;
        return $this;
    }

    /**
     * @param string $spellcheck
     * @return HtmlaElement
     */
    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    /**
     * @param array $style
     * @return HtmlaElement
     */
    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @param string $tabindex
     * @return HtmlaElement
     */
    public function tabindex(string $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    /**
     * @param string $title
     * @return HtmlaElement
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $translate
     * @return HtmlaElement
     */
    public function translate(string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }

    /**
     * @param string $href
     * @return HtmlaElement
     */
    public function href(string $href): self
    {
        $this->href = $href;
        return $this;
    }
}