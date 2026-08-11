<?php

declare(strict_types=1);

abstract class AbstractHtmlElement extends AbstractHtmlComponent
{
    protected ?TokenInterface $token;
    protected bool $includeToken = true;

    public function __construct(
        ?TokenInterface $token = null,
        ?TranslatorServiceInterface $translator = null,
    ) {
        $this->token = $token;
        parent::__construct($translator);
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
                $this->handleErrorMessage($child);
            }
        }

        [$begin, $end] = $this->getContentContext();

        if ($this->hasErrorMessage()) {
            if ($this->hasInputBoxContainer()) {
                $results[] = $this->errorMessage;
                $this->errorMessage = '';
            } elseif ($this->parent !== null) {
                $this->parent->errorMessage($this->errorMessage);
                $this->parent->class('has-error');
                $this->errorMessage = '';
            }
        }

        $inner = $this->contentUp
            ? ($this->content ?? '') . implode('', $results)
            : implode('', $results) . ($this->content ?? '');

        return $begin . $this->frmName() . $inner . $end;
    }

    #[Override]
    public function add(AbstractHtmlComponent|null ...$formElements): static
    {
        foreach ($formElements as $formElement) {
            if ($formElement !== null) {
                $this->children->add($formElement);
                $formElement->setParent($this);
            }
        }
        return $this;
    }

    #[Override]
    public function remove(AbstractHtmlComponent $component): static
    {
        $this->children->removeByValue($component);
        $component->setParent(null);
        return $this;
    }

    public function isComposite(): bool
    {
        return true;
    }

    // ────────────────────────────────────────────────────────────────
    //  Tag Rendering
    // ────────────────────────────────────────────────────────────────

    protected function tagContentContext(string $tag, string $children): string
    {
        if (isset($this->content)) {
            return $this->contentUp
                ? $tag . $this->content . $children
                : $tag . $children . $this->content;
        }
        return $tag . $children;
    }

    protected function begin(): string
    {
        $properties = get_object_vars($this);

        if ($this instanceof HtmlAttributeProviderInterface) {
            $properties = array_merge($properties, $this->getProperties());
        }

        $tag = $this->getTagAttributes($properties, $this->tag);

        if ($this->tag === 'form' && $this->includeToken) {
            $tag .= $this->csrftoken();
        }

        return $tag;
    }

    protected function end(): string
    {
        if (empty($this->tag)) {
            return '';
        }
        if (in_array(strtolower($this->tag), self::VOID_TAGS, true)) {
            return '';
        }
        return "</{$this->tag}>";
    }

    // ────────────────────────────────────────────────────────────────
    //  Private Helpers
    // ────────────────────────────────────────────────────────────────

    private function handleErrorMessage(AbstractHtmlComponent $child): void
    {
        if (!($child instanceof AbstractInput || $child instanceof SelectElement || $child instanceof TextAreaElement)) {
            return;
        }

        $parent = $child->getParent();
        if ($parent === null) {
            return;
        }

        if (!$child->hasInputBoxContainer()) {
            $this->handleErrorMessage($parent);
            return;
        }

        $parent->class('has-error');
        $parent->errorMessage($child->getErrorMessage());
    }

    private function getContentContext(): array
    {
        return [$this->begin(), $this->end()];
    }

    private function frmName(): string
    {
        if ($this->tag !== 'form' || !$this->includeToken) {
            return '';
        }
        return (new HiddenType())
            ->name('frm_name')
            ->value($this->name ?? '')
            ->generate();
    }

    private function csrftoken(): string
    {
        if ($this->tag !== 'form') {
            return '';
        }
        return (new HiddenType())
            ->name('csrfToken')
            ->value($this->token->getCsrfHash(8, $this->name ?? ''))
            ->generate();
    }
}