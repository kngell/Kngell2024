<?php

declare(strict_types=1);

class CustomHtmlElement extends FormElement
{
    private string $htmlTag;
    private array $class;
    private string $id;
    private mixed $content;

    public function __construct(string $htmlTag)
    {
        $this->htmlTag = $htmlTag;
    }

    public function makeForm(): string
    {
        $tag = '<' . $this->htmlTag;
        foreach ($this->__toArray() as $key => $value) {
            if ($key !== $this->content && in_array(gettype($value), ['string', 'bool', 'boolean', 'array', 'integer']) && $value !== '') {
                $tag .= $this->formElementAttribute($key, $value);
            }
        }
        $tag .= '>';
        $tag .= $this->content;
        return $tag . '</' . $this->htmlTag . '>';
    }

    /**
     * @param string $htmlTag
     * @return CustomHtmlElement
     */
    public function htmlTag(string $htmlTag): self
    {
        $this->htmlTag = $htmlTag;
        return $this;
    }

    /**
     * @param array $class
     * @return CustomHtmlElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $id
     * @return CustomHtmlElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $content
     * @return CustomHtmlElement
     */
    public function content(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }
}
