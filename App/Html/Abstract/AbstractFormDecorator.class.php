<?php

declare(strict_types=1);

abstract class AbstractFormDecorator extends AbstractHtmlDecorator implements RuntimeConfigurableInterface
{
    use RuntimeConfigurableTrait;

    protected const array HEADER_BTN_CONFIG = [];
    protected const array BREADCRUMBS_LINKS = [];

    protected string $action = '';
    protected array|Entity $formValues = [];
    protected array $formErrors = [];
    protected array $files = [];

    public function __construct(
        private readonly AdminMainHeaderFactory $factory,
    ) {
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

    private function buildHeaderSection(Controller $target): array
    {
        if (empty(static::BREADCRUMBS_LINKS)) {
            return [];
        }

        $headerKey = $this->getHeaderKey();

        if ($headerKey === null) {
            return [];
        }

        $adminMainHeader = $this->factory->create($target->getBuilder());

        if ($this->formValues instanceof Entity && $this->formValues->entityKeyIsInitialzed()) {
            $adminMainHeader
                ->isEditMode()
                ->idFieldName($this->formValues->getEntityKeyField())
                ->id($this->formValues->getEntityPrimarykeyValue());
        }

        $component = $adminMainHeader
            ->withTitle($this->headerTitle())
            ->withButtons(static::HEADER_BTN_CONFIG)
            ->withBreadcrumbs(static::BREADCRUMBS_LINKS)
            ->build();

        if ($component === null) {
            return [];
        }

        return [$headerKey => $component->generate()];
    }
}