<?php

declare(strict_types=1);
abstract class AbstractHtmlElement extends AbstractHtmlComponent
{
    protected CollectionInterface $children;
    protected ?TokenInterface $token;
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

            if ($child instanceof AbstractInput || $child instanceof SelectElement || $child instanceof TextAreaElement) {
                $child->populateField();
            }

            $results[] = $child->generate();
            if ($child->hasErrorMessage()) {
                // $this->errorMessage = $child->getErrorMessage();
                $this->handleErrorMessage($child);
            }
        }
        [$begin, $end] = $this->getContentContext();

        if ($this->hasErrorMessage()) {
            // $results[] = $this->errorMessage;
            if ($this->hasInputBoxContainer()) {
                $results[] = $this->errorMessage;
                $this->errorMessage = '';
            } else {
                $this->parent->errormessage($this->errorMessage);
                $this->parent->class('has-error');
                $this->errorMessage = '';
            }
        }

        if ($this->contentUp) {
            // Inline content first, then children
            $inner = ($this->content ?? '') . implode('', $results);
        } else {
            // Children first, then inline content
            $inner = implode('', $results) . ($this->content ?? '');
        }
        return $begin . $this->frmName() . $inner . $end;
    }

    /**
     * @param AbstractHtmlComponent|null ...$formElements
     *
     * @return AbstractHtmlComponent
     */
    #[Override]
    public function add(AbstractHtmlComponent|null ...$formElements): AbstractHtmlComponent
    {
        /** @var AbstractHtmlComponent $formElement */
        foreach ($formElements as $formElement) {
            !is_null($formElement) ? $this->addFormElement($formElement) : '';
        }
        return $this;
    }

    /**
     * @param AbstractHtmlComponent $component
     *
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

    protected function tagContentContext(string $tag, string $content): string
    {
        if (isset($this->content)) {
            if ($this->contentUp) {
                return $tag . $this->content . $content;
            } else {
                return $tag . $content . $this->content;
            }
        }
        return $tag . $content;
    }

    protected function addFormElement(AbstractHtmlComponent $component): self
    {
        $this->children->add($component);
        $component->setParent($this);
        return $this;
    }

    private function handleErrorMessage(AbstractHtmlComponent $child): void
    {
        if ($child instanceof AbstractInput || $child instanceof SelectElement || $child instanceof TextAreaElement) {
            if (!$child->hasInputBoxContainer()) {
                $this->handleErrorMessage($child->getParent());
            }
            $child->getParent()->class('has-error');
            $child->getParent()->errormessage($child->getErrorMessage());
        }
    }

    private function getContentContext(): array
    {
        $begin = $this->begin();
        $end = $this->end();
        return [$begin, $end];
    }

    private function end(): string
    {
        // void tags have no closing
        if (in_array(strtolower($this->tag), self::VOID_TAGS, true)) {
            return '';
        }

        return "</{$this->tag}>";
    }

    private function frmName(): string
    {
        if ($this->tag !== 'form') {
            return '';
        }

        return (new HiddenType())
            ->name('frm_name')
            ->value($this->name ?? '')
            ->generate();
    }

    private function begin(): string
    {
        $tag = $this->getTagAttributes(get_object_vars($this), $this->tag);

        // inject csrf only for forms
        if ($this->tag === 'form') {
            $tag .= $this->csrftoken();
        }

        return $tag;
    }

    private function csrftoken(): string
    {
        if ($this->tag !== 'form') {
            return '';
        }
        return (new HiddenType())->name('csrfToken')->value($this->token->getCsrfHash(8, $this->name ?? ''))->generate();
    }
}