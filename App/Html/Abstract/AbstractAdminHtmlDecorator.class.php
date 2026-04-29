<?php

declare(strict_types=1);

abstract class AbstractAdminHtmlDecorator extends AbstractHtmlDecorator
{
    protected const array HEADER_BTN_CONFIG = [];
    protected const array BREADCRUMBS_LINKS = [];

    protected array|Entity $formValues = [];
    protected string $deleteAction = '';
    protected bool $withFilters = false;

    public function __construct(
        private readonly AdminMainHeaderFactory $factory,
    ) {
        parent::__construct();
    }

    protected function buildHeaderSection(Controller $target): array
    {
        if (empty(static::BREADCRUMBS_LINKS)) {
            return [];
        }

        $headerKey = $this->getHeaderKey();

        if ($headerKey === null) {
            return [];
        }

        $adminMainHeader = $this->factory->create($target->getBuilder());

        $headerSearchAndFilter = null;
        if ($this->withFilters) {
            $headerSearchAndFilter = $this->factory->createSubHeader($target->getBuilder())->build();
        }

        if ($this->formValues instanceof Entity && $this->formValues->entityKeyIsInitialzed()) {
            $adminMainHeader
                ->isEditMode()
                ->idFieldName($this->formValues->getEntityKeyField())
                ->id($this->formValues->getEntityPrimarykeyValue());
        }

        $headerBtnConfig = static::HEADER_BTN_CONFIG;
        if ($this->deleteAction !== '') {
            $headerBtnConfig[0]['action'] = $this->deleteAction;
        }

        $component = $adminMainHeader
            ->withTitle($this->headerTitle())
            ->withButtons($headerBtnConfig)
            ->withBreadcrumbs(static::BREADCRUMBS_LINKS)
            ->withWrapperClass($this->getWrapperClass())
            ->withTitleClass($this->getTitleClass())
            ->build();

        if ($component === null) {
            return [];
        }

        $header = [
            $headerKey => $component->generate(),
        ];

        if ($this->withFilters && $headerSearchAndFilter !== null) {
            $header['headerSearchAndFilter'] = $headerSearchAndFilter->generate();
        }

        return $header;
    }

    protected function getWrapperClass(): array
    {
        return [];
    }

    protected function getTitleClass(): array
    {
        return [];
    }

    protected function getHeaderKey(): ?string
    {
        return 'adminMainHeader';
    }

    protected function headerTitle(): ?string
    {
        return 'Header Title'; // Tip: Override this in concrete classes!
    }
}