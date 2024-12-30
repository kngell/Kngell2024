<?php

declare(strict_types=1);
abstract class AbstractHtmlElement extends AbstractHtmlComponent
{
    protected CollectionInterface $children;
    protected TokenInterface $token;

    public function __construct(TokenInterface $token)
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

    private function frmName() : string
    {
        return (new HiddenType)->name('frm_name')->value($this->name ?? '')->generate();
    }

    private function end() : string
    {
        return '</form>';
    }

    private function begin() : string
    {
        $formBegin = '<form';
        foreach ($this as $prop => $value) {
            if ($prop !== 'formErrors' && $prop !== 'formValues') {
                $formBegin .= match (true) {
                    in_array(gettype($value), ['string', 'bool', 'boolean']) => ' ' . $this->property($prop, $value),
                    is_array($value) => ' ' . $this->property($prop, implode(' ', $value)),
                    default => ''
                };
            }
        }
        return $formBegin . '>' . $this->csrftoken();
    }

    private function property(string $prop, string|bool $value) : string
    {
        return match ($prop) {
            'action' => $prop . '="/' . $value . '"',
            'novalidate' => 'novalidate',
            'acceptCharset' => 'accept-charset="' . $value . '"',
            'enctype' => $prop . "='multipart/form-data'",
            'class' => 'class="' . $value . '"',
            default => $prop . '="' . $value . '"',
        };
    }

    private function csrftoken() : string
    {
        return (new HiddenType)->name('csrfToken')->value($this->token->getCsrfHash(8, $this->name ?? ''))->generate();
    }

    private function addFormElement(AbstractHtmlComponent $component): self
    {
        $this->children->add($component);
        $component->setParent($this);
        return $this;
    }
}