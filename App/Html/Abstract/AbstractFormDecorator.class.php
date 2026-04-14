<?php

declare(strict_types=1);

abstract class AbstractFormDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        Controller $controller,
        protected string $action,
        protected array|Entity $formValues = [],
        protected array $formErrors = [],
        protected array $files = [],
    ) {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $target = $this->getTarget();
        $this->beforeRender();

        $formHtml = $target->form($this->action, $this->formValues, $this->formErrors, $this->files);
        $formHtml = $this->afterRender($formHtml);
        return parent::page() + [$this->getFormKey() => $formHtml];
    }

    abstract protected function getFormKey(): string;

    protected function getForm(): ?FormTemplateInterface
    {
        return null;
    }

    protected function beforeRender(): void
    {
        // Hook for child classes
    }

    protected function afterRender(string $formHtml): string
    {
        return $formHtml;
    }
}