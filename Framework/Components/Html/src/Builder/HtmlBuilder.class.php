<?php

declare(strict_types=1);

class HtmlBuilder extends AbstractHtmlElement
{
    use ElementFactoryTrait;

    private const GENERIC_TAGS = [
        'div', 'section', 'body', 'nav', 'ul', 'li', 'dl',
        'table', 'thead', 'tbody', 'tr', 'td', 'span', 'th',
        'button', 'small', 'svg', 'use', 'caption', 'colgroup',
        'col', 'code', 'address', 'aside', 'dialog',
        // Add HTML_TAG_ELEMENTS here since they return the same
        'p', 'dd', 'dt', 'img', 'video', 'i', 'strong', 'desc',
    ];

    public function __construct(
        TokenInterface $token,
        ?TranslatorServiceInterface $translator = null,
        string $tag = '',
    ) {
        $this->tag = $tag;
        parent::__construct($token, $translator);
    }

    public function form(bool $includeCsrftoken = true): FormBuilder
    {
        return new FormBuilder($this->token, $this->translator, $includeCsrftoken);
    }

    public function textarea(string $content = ''): TextAreaElement
    {
        return new TextAreaElement($content);
    }

    public function div(): static
    {
        return $this->tag('div');
    }

    public function nav(): static
    {
        return $this->tag('nav');
    }

    public function link(): HtmlLinkElement
    {
        return new HtmlLinkElement();
    }

    public function span(): static
    {
        return $this->tag('span');
    }

    public function select(): SelectElement
    {
        return new SelectElement();
    }

    public function tag(string $tag): AbstractHtmlElement
    {
        return match ($tag) {
            'a' => new HtmlLinkElement(),
            'select' => new SelectElement(),
            default => new self($this->token, $this->translator, $tag)
        };
    }

    private function isGenericTag(string $tag): bool
    {
        return in_array($tag, self::GENERIC_TAGS, true)
            || preg_match('~[0-9]+~', $tag);
    }
}
