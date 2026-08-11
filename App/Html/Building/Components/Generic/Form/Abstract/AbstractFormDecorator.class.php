<?php

declare(strict_types=1);

abstract class AbstractFormDecorator extends AbstractAdminHtmlDecorator
{
    protected const array HEADER_BTN_CONFIG = [];
    protected const array BREADCRUMBS_LINKS = [];
    private const string ASSET_KEY = 'formAsset';

    protected ?AbstractFormConfigFactory $factory = null;
    protected ?ModalFormBuilderInterface $modalBuilder = null;
    protected ?FormConfig $formConfig = null;
    protected string $action = '';
    protected string $deleteAction = '';
    protected ?string $cancelUrl = null;
    protected ?string $blockType = null;
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

        if ($this->factory !== null) {
            $this->formConfig = $this->factory->createFormConfig();
            $target->getForm()->setFormConfig($this->formConfig);
        }
        $formHtml = $target->form(
            $this->action,
            $this->formValues,
            $this->formErrors,
            $this->files,
        );

        if ($this->modalBuilder !== null) {
            $modalHtml = $this->modalBuilder->build(
                $this->action,
                $formHtml,
                $this->formConfig,
            );
            $identifier = $this->modalBuilder->getIdentier();
            return parent::page() + [
                $identifier => $modalHtml,
            ];
        }

        $formHtml = $this->afterRender($formHtml);

        $page = [];
        if ($this->formConfig !== null && $this->formConfig->shouldShowFormHeader()) {
            $page = $this->buildHeaderSection($target);
        }

        $formAsset = $this->formConfig->getAssets();
        if (!empty($formAsset)) {
            $formAsset = [self::ASSET_KEY => $formAsset];
        }

        return parent::page() + $page + $formAsset + [$this->getDisplayKey() ?? 'mainForm' => $formHtml];
    }

    abstract protected function getDisplayKey(): ?string;

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

    protected function formConfig(): ?FormConfig
    {
        return null;
    }
}