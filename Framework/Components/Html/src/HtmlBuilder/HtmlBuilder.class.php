<?php

declare(strict_types=1);

class HtmlBuilder extends AbstractHtmlComponent
{
    protected CollectionInterface $children;
    private string $align;
    private string $onclick;
    private string $ondblclick;
    private string $onmousedown;
    private string $onmouseup;
    private string $onmouseover;
    private string $onmousemove;
    private string $onmouseout;
    private string $onkeypress;
    private string $onkeydown;
    private string $onkeyup;
    private string $content;
    private string $tag;

    public function __construct(string $tag = '')
    {
        $this->children = new Collection();
        $this->tag = $tag;
    }

    public function generate(): string
    {
        $results = [];
        /** @var AbstractHtmlComponent $child */
        foreach ($this->children as $child) {
            if ($child instanceof AbstractForm) {
                $results[] = $child->makeForm();
            } else {
                $results[] = $child->generate();
            }
        }
        return $this->begin() . implode(' ', $results) . $this->end();
    }

    #[Override]
    public function add(AbstractHtmlComponent|AbstractForm|null ...$htmlelements): AbstractHtmlComponent
    {
        foreach ($htmlelements as $htmlElement) {
            ! is_null($htmlElement) ? $this->addFormElement($htmlElement) : '';
        }
        return $this;
    }

    public function remove(AbstractHtmlComponent $component): AbstractHtmlComponent
    {
        $this->children->removeByValue($component);
        return $this;
    }

    public function form(TokenInterface $token) : FormBuilder
    {
        return new FormBuilder($token);
    }

    public function htmlTag(string $tag) : self|HtmlaElement|HtmlTagElement
    {
        return match (true) {
            in_array($tag, ['div', 'section', 'body']) => new self($tag),
            $tag === 'a' => new HtmlaElement(),
            $tag === 'p' || preg_match('~[0-9]+~', $tag) => new HtmlTagElement($tag),
        };
    }

    /**
     * @param array $class
     * @return HtmlDivElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $title
     * @return HtmlDivElement
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $align
     * @return HtmlDivElement
     */
    public function align(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    /**
     * @param string $onclick
     * @return HtmlDivElement
     */
    public function onclick(string $onclick): self
    {
        $this->onclick = $onclick;
        return $this;
    }

    /**
     * @param string $ondblclick
     * @return HtmlDivElement
     */
    public function ondblclick(string $ondblclick): self
    {
        $this->ondblclick = $ondblclick;
        return $this;
    }

    /**
     * @param string $onmousedown
     * @return HtmlDivElement
     */
    public function onmousedown(string $onmousedown): self
    {
        $this->onmousedown = $onmousedown;
        return $this;
    }

    /**
     * @param string $onmouseup
     * @return HtmlDivElement
     */
    public function onmouseup(string $onmouseup): self
    {
        $this->onmouseup = $onmouseup;
        return $this;
    }

    /**
     * @param string $onmouseover
     * @return HtmlDivElement
     */
    public function onmouseover(string $onmouseover): self
    {
        $this->onmouseover = $onmouseover;
        return $this;
    }

    /**
     * @param string $onmousemove
     * @return HtmlDivElement
     */
    public function onmousemove(string $onmousemove): self
    {
        $this->onmousemove = $onmousemove;
        return $this;
    }

    /**
     * @param string $onmouseout
     * @return HtmlDivElement
     */
    public function onmouseout(string $onmouseout): self
    {
        $this->onmouseout = $onmouseout;
        return $this;
    }

    /**
     * @param string $onkeypress
     * @return HtmlDivElement
     */
    public function onkeypress(string $onkeypress): self
    {
        $this->onkeypress = $onkeypress;
        return $this;
    }

    /**
     * @param string $onkeydown
     * @return HtmlDivElement
     */
    public function onkeydown(string $onkeydown): self
    {
        $this->onkeydown = $onkeydown;
        return $this;
    }

    /**
     * @param string $onkeyup
     * @return HtmlDivElement
     */
    public function onkeyup(string $onkeyup): self
    {
        $this->onkeyup = $onkeyup;
        return $this;
    }

    /**
     * @param string $content
     * @return HtmlDivElement
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $accesskey
     * @return HtmlDivElement
     */
    public function accesskey(string $accesskey): self
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    /**
     * @param string $contenteditable
     * @return HtmlDivElement
     */
    public function contenteditable(string $contenteditable): self
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    /**
     * @param string $data
     * @return HtmlDivElement
     */
    public function data(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $dir
     * @return HtmlDivElement
     */
    public function dir(string $dir): self
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param string $draggable
     * @return HtmlDivElement
     */
    public function draggable(string $draggable): self
    {
        $this->draggable = $draggable;
        return $this;
    }

    /**
     * @param string $enterkeyhint
     * @return HtmlDivElement
     */
    public function enterkeyhint(string $enterkeyhint): self
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    /**
     * @param string $hidden
     * @return HtmlDivElement
     */
    public function hidden(string $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @param string $id
     * @return HtmlDivElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $inert
     * @return HtmlDivElement
     */
    public function inert(string $inert): self
    {
        $this->inert = $inert;
        return $this;
    }

    /**
     * @param string $inputmode
     * @return HtmlDivElement
     */
    public function inputmode(string $inputmode): self
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    /**
     * @param string $lang
     * @return HtmlDivElement
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param string $popover
     * @return HtmlDivElement
     */
    public function popover(string $popover): self
    {
        $this->popover = $popover;
        return $this;
    }

    /**
     * @param string $spellcheck
     * @return HtmlDivElement
     */
    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    /**
     * @param array $style
     * @return HtmlDivElement
     */
    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @param string $tabindex
     * @return HtmlDivElement
     */
    public function tabindex(string $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    /**
     * @param string $translate
     * @return HtmlDivElement
     */
    public function translate(string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }

    private function begin() : string
    {
        $tag = '<' . $this->tag;
        foreach ($this as $key => $value) {
            if ($key === 'content' || is_object($value) || $key === 'tag') {
                continue;
            }
            if (in_array(gettype($value), ['string', 'array'])) {
                $tag .= $this->getTagAttribute($key, $value);
            }
        }
        $tag .= '>';
        return $tag;
    }

    private function end() : string
    {
        return match ($this->tag) {
            'div' => '</div>',
            'section' => '</section>',
        };
    }

    private function addFormElement(AbstractHtmlComponent|AbstractForm|null $htmlElement): self
    {
        $this->children->add($htmlElement);
        $htmlElement->setParent($this);
        return $this;
    }
}