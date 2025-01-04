<?php

declare(strict_types=1);

class HtmlBuilder extends AbstractHtmlElement
{
    public function __construct(TokenInterface $token, string $tag = '')
    {
        $this->tag = $tag;
        parent::__construct($token);
    }

    public function form() : FormBuilder
    {
        return new FormBuilder($this->token);
    }

    public function tag(string $tag) : self|HtmlaElement|HtmlTagElement
    {
        return match (true) {
            in_array($tag, ['div', 'section', 'body', 'nav', 'ul', 'li', 'dl']) => new self($this->token, $tag),
            $tag === 'a' => new HtmlaElement(),
            in_array($tag, ['p', 'span', 'dd', 'dt']) || preg_match('~[0-9]+~', $tag) => new HtmlTagElement($tag),
        };
    }

    public function button(string $type = '') : ButtonElement
    {
        return new ButtonElement($type);
    }

    /**
     * @param array $custom
     * @return HtmlBuilder
     */
    public function custom(array $custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * @param array $formErrors
     * @return HtmlBuilder
     */
    #[Override]
    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    /**
     * @param array $formValues
     * @return HtmlBuilder
     */
    #[Override]
    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    public function onclick(string $onclick): self
    {
        $this->onclick = $onclick;
        return $this;
    }

    public function ondblclick(string $ondblclick): self
    {
        $this->ondblclick = $ondblclick;
        return $this;
    }

    public function onmousedown(string $onmousedown): self
    {
        $this->onmousedown = $onmousedown;
        return $this;
    }

    public function onmouseup(string $onmouseup): self
    {
        $this->onmouseup = $onmouseup;
        return $this;
    }

    public function onmouseover(string $onmouseover): self
    {
        $this->onmouseover = $onmouseover;
        return $this;
    }

    public function onmousemove(string $onmousemove): self
    {
        $this->onmousemove = $onmousemove;
        return $this;
    }

    public function onmouseout(string $onmouseout): self
    {
        $this->onmouseout = $onmouseout;
        return $this;
    }

    public function onkeypress(string $onkeypress): self
    {
        $this->onkeypress = $onkeypress;
        return $this;
    }

    public function onkeydown(string $onkeydown): self
    {
        $this->onkeydown = $onkeydown;
        return $this;
    }

    public function onkeyup(string $onkeyup): self
    {
        $this->onkeyup = $onkeyup;
        return $this;
    }

    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function accesskey(string $accesskey): self
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    public function contenteditable(string $contenteditable): self
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    public function data(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function dir(string $dir): self
    {
        $this->dir = $dir;
        return $this;
    }

    public function draggable(string $draggable): self
    {
        $this->draggable = $draggable;
        return $this;
    }

    public function enterkeyhint(string $enterkeyhint): self
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    public function hidden(string $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function inert(string $inert): self
    {
        $this->inert = $inert;
        return $this;
    }

    public function inputmode(string $inputmode): self
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function popover(string $popover): self
    {
        $this->popover = $popover;
        return $this;
    }

    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function tabindex(string $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    public function translate(string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }
}
