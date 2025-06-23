<?php

declare(strict_types=1);

class LabelElement extends AbstractHtmlElement
{
    protected string $for;
    protected string $formId;

    public function __construct(string|null $content = null)
    {
        parent::__construct();
        $this->content = $content;
        $this->tag = 'label';
    }

    /**
     * @param array $formErrors
     * @return LabelElement
     */
    #[Override]
    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    /**
     * @param array $formValues
     * @return LabelElement
     */
    #[Override]
    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    /**
     * @param string $content
     * @param bool $contentUp
     * @return LabelElement
     */
    public function content(string $content, bool $contentUp = true): self
    {
        $this->content = $content;
        $this->contentUp = $contentUp;
        return $this;
    }

    /**
     * @param string $for
     * @return LabelElement
     */
    public function for(string $for): self
    {
        $this->for = $for;
        return $this;
    }

    /**
     * @param string $formId
     * @return LabelElement
     */
    public function formId(string $formId): self
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * @param string $id
     * @return LabelElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string ...$class
     * @return LabelElement
     */
    public function class(string ...$class): self
    {
        $this->class = array_merge($this->class, $class);
        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }
}