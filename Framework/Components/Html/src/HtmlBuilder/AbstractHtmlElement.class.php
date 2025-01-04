<?php

declare(strict_types=1);
abstract class AbstractHtmlElement extends AbstractHtmlComponent
{
    protected CollectionInterface $children;
    protected ?TokenInterface $token;
    protected ?string $content;
    protected string $tag;

    public function __construct(?TokenInterface $token = null)
    {
        $this->children = new Collection();
        $this->token = $token;
    }

    public function generate(): string
    {
        $results = [];
        /** @var AbstractHtmlComponent $child */
        foreach ($this->children as $child) {
            $child->formErrors($this->formErrors);
            $child->formValues($this->formValues);
            $results[] = $child->generate();
        }
        return $this->begin() . $this->frmName() . implode(' ', $results) . $this->end();
    }

    /**
     * @param AbstractHtmlComponent|null ...$formElements
     * @return AbstractHtmlComponent
     */
    #[Override]
    public function add(AbstractHtmlComponent|null ...$formElements) : AbstractHtmlComponent
    {
        /** @var AbstractHtmlComponent $formElement */
        foreach ($formElements as $formElement) {
            ! is_null($formElement) ? $this->addFormElement($formElement) : '';
        }
        return $this;
    }

    /**
     * @param AbstractHtmlComponent $component
     * @return AbstractFormElement
     */
    public function remove(AbstractHtmlComponent $component): self
    {
        $this->children->removeByValue($component);
        $component->setParent(null);
        return $this;
    }

    public function isComposite(): bool
    {
        return true;
    }

    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    private function end() : string
    {
        $tag = '';
        if (isset($this->content)) {
            $tag .= $this->content;
        }
        return $tag . (string) '</' . $this->tag . '>';
    }

    private function frmName() : string
    {
        if ($this->tag !== 'form') {
            return '';
        }
        return (new HiddenType)->name('frm_name')->value($this->name ?? '')->generate();
    }

    private function begin() : string
    {
        $tag = '<' . $this->tag;
        foreach ($this as $key => $value) {
            if (in_array($key, ['content', 'tag', 'formErrors', 'formValues', 'token']) || is_object($value)) {
                continue;
            }
            // if (in_array(gettype($value), ['string', 'array'])) {
            $tag .= $this->getTagAttribute($key, $value);
            // }
        }
        $tag .= '>';

        return $tag . $this->csrftoken();
    }

    private function csrftoken() : string
    {
        if ($this->tag !== 'form') {
            return '';
        }
        return (new HiddenType)->name('csrfToken')->value($this->token->getCsrfHash(8, $this->name ?? ''))->generate();
    }

    private function addFormElement(AbstractHtmlComponent $component): self
    {
        $this->children->add($component);
        $component->setParent($this);
        return $this;
    }
}