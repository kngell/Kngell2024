<?php

declare(strict_types=1);

abstract class AbstractFormDecorator extends AbstractAdminHtmlDecorator
{
    protected const array HEADER_BTN_CONFIG = [];
    protected const array BREADCRUMBS_LINKS = [];

    protected string $action = '';
    protected string $deleteAction = '';
    protected array|Entity $formValues = [];
    protected array $formErrors = [];
    protected array $files = [];

    public function __construct(
        AdminMainHeaderFactory $factory,
    ) {
        parent::__construct($factory);
    }

    public function page(): array
    {
        $target = $this->getTarget();
        $this->beforeRender();

        $formHtml = $target->form($this->action, $this->formValues, $this->formErrors, $this->files);
        $formHtml = $this->afterRender($formHtml);

        $page = $this->buildHeaderSection($target);

        return parent::page() + $page + [$this->getFormKey() => $formHtml];
    }

    abstract protected function getFormKey(): string;

    protected function getHeaderKey(): ?string
    {
        return null;
    }

    protected function headerTitle(): ?string
    {
        return null;
    }

    protected function beforeRender(): void
    {
    }

    protected function afterRender(string $formHtml): string
    {
        return $formHtml;
    }
}