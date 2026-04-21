<?php

declare(strict_types=1);

class HtmlBuilder extends AbstractHtmlElement
{
    use ElementFactoryTrait;

    public function __construct(TokenInterface $token, string $tag = '')
    {
        $this->tag = $tag;
        parent::__construct($token);
    }

    public function form(bool $includeCsrftoken = true): FormBuilder
    {
        return new FormBuilder($this->token, $includeCsrftoken);
    }

    public function textarea(string $content = ''): TextAreaElement
    {
        try {
            return new TextAreaElement($content);
        } catch (Throwable $th) {
            throw new FormElementNotFound(TextAreaElement::class);
        }
    }

    public function div(): static
    {
        return new self($this->token, 'div');
    }

    public function nav(): static
    {
        return new static($this->token, 'nav');
    }

    public function tag(string $tag): self|HtmlaElement|HtmlTagElement|SelectElement
    {
        return match (true) {
            in_array($tag, [
                'div', 'section', 'body', 'nav', 'ul', 'li', 'dl',
                'table', 'thead', 'tbody', 'tr', 'td', 'span', 'th',
                'button', 'small', 'svg', 'use', 'caption', 'colgroup',
                'col', 'code',
            ]) || preg_match('~[0-9]+~', $tag)
                => new self($this->token, $tag),
            $tag === 'a'
                => new HtmlaElement(),
            in_array($tag, ['p', 'dd', 'dt', 'img', 'video', 'i', 'strong', 'desc'])
                => new HtmlTagElement($tag),
            $tag === 'select'
                => new SelectElement($this->token),
        };
    }
}