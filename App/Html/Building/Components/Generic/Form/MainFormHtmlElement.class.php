<?php

declare(strict_types=1);

class MainFormHtmlElement extends AbstractForm
{
    use MapperExtractorTrait;

    private ?FormLayoutInterface $layoutBuilder;

    public function __construct(
        FormConfig $config,
        FileMetadataService $metadataService,
        HtmlBuilder $builder,
        FlashRenderer $flashRenderer,
        IconBuilder $iconBuilder,
        ButtonBuilder $buttonBuilder,
        private FormSectionProvider $provider,
        private ArrayFieldMapper $arrayFieldMapper,
        HtmlFormSectionManager $sectionManager,
    ) {
        parent::__construct(
            config: $config,
            builder: $builder,
            flashRenderer: $flashRenderer,
            iconBuilder: $iconBuilder,
            buttonBuilder: $buttonBuilder,
            metadataService: $metadataService,
            sectionManager: $sectionManager,
        );

        $this->layoutBuilder = $config->getLayoutBuilder();
    }

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $html = $this->builder;

        // Register sections with the manager
        $this->provider->registerSections($this->builder, $this->sectionManager);
        $hiddenFieldMapping = $this->hiddenFieldMapping();

        // $this->formValues = $formValues;
        $mapping = new FieldMappingPayload(
            fieldMapping: array_merge(
                $hiddenFieldMapping,
                $this->sectionManager->getFieldMapping($formValues),
            ),
        );
        // dd($mapping, $formValues);
        // Handle entity mapping
        if ($formValues instanceof Entity) {
            $this->formValues = $formValues->toFormArray($mapping);
        } else {
            $this->formValues = $this->arrayFieldMapper->toFormArray(array_merge($formValues, $files), $mapping);
        }

        $this->files = $files;
        $this->formErrors = $formErrors;

        // Build form HTML parts
        $htmlParts = [];

        if ($this->config->hasFormContainerClass()) {
            $htmlParts[] = $html->tag('div')
                ->class(...$this->config->getFormContainerClass())
                ->add(
                    $html->htmlBlock($this->flashRenderer->render()),
                    $html->htmlBlock(parent::make($action, $this->formValues, $this->formErrors, $this->files)),
                )->generate();
        } else {
            $htmlParts[] = $html->htmlBlock($this->flashRenderer->render())->generate();
            $htmlParts[] = $html->htmlBlock(parent::make($action, $this->formValues, $this->formErrors, $this->files))->generate();
        }

        // Footer with submit button (if not disabled)
        if ($this->config->isFooterEnabled()) {
            $isStandAlone = $this->config->isStandAloneFooter();
            if ($isStandAlone) {
                $footer = $this->getStandAloneFooter(
                    action: $action,
                );
            } else {
                $footer = $this->getInlineFooter();
            }

            $htmlParts[] = $footer->renderFooter();
        }

        return implode('', $htmlParts);
    }

    public function buildLayout(?HtmlBuilder $form = null): array
    {
        $hiddeFields = $this->config->getHiddenFields();
        $fieldRenderer = $this->config->getFieldRenderer();
        $hiddenFieldsConponents = [];
        foreach ($hiddeFields as $field) {
            if ($field instanceof FormFieldConfig) {
                $field = $field->toArray();
            }
            $hiddenFieldsConponents[] = $fieldRenderer->render(
                $field,
                $form->form(),
                $this,
                $this->config,
            );
        }

        $sections = $this->layoutBuilder->buildLayout(
            formValues: $this->formValues,
            sectionManager: $this->sectionManager,
            sectionRenderer: $this->sectionRenderer,
            builder: $form,
            formInstance: $this,
            config: $this->config,
        );
        $sectionParent = $this->config->getSectionParent();
        if ($sectionParent !== null) {
            $customLayout = $sectionParent->getSectionsCustomLayout($sections);
            if ($customLayout) {
                return array_merge($hiddenFieldsConponents, [$customLayout]);
            }
        }
        return array_merge($hiddenFieldsConponents, $sections);
    }

    public function getDefaultInputLayoutName(): ?string
    {
        return $this->config->getDefaultInputLayoutName();
    }

    protected function getFormCustomAttributes(): array
    {
        $attributes = $this->config->getCustomAttributes();

        if (!isset($attributes['data-validate'])) {
            $attributes['data-validate'] = 'true';
        }

        return $attributes;
    }

    protected function getFieldHandlers(): array
    {
        $configHandlers = $this->config->getFieldHandlers();
        if (!empty($configHandlers)) {
            return $configHandlers;
        }
        return [];
    }

    protected function getFormName(): string
    {
        $formName = $this->config->getFormName();
        if ($formName) {
            return $formName;
        }

        return str_replace('_', '-', $this->config->getFormKey()) . '-frm';
    }

    protected function getFormClass(): array
    {
        $formClass = $this->config->getFormClass();
        if (!empty($formClass)) {
            return $formClass;
        }

        return [str_replace('_', '-', $this->config->getFormKey()) . '-frm'];
    }

    protected function getFormKey(): string
    {
        return $this->config->getFormKey();
    }

    protected function beforeRender(): void
    {
        $callback = $this->config->getBeforeRenderCallback();
        if ($callback !== null) {
            call_user_func($callback, $this, $this->config);
        }
    }

    private function getInlineFooter(): FooterProvider
    {
        return new FooterProvider(
            builder: $this->builder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forInlineForm(
                formId: $this->config->getFormId(),
                footerClass: $this->config->getFooterClass() ?: [$this->getFormKey() . '__footer'],
                renderProgressBar: $this->config->isShowProgressBar(),
                submitText: $this->config->getSubmitText(),
                submitIcon: $this->config->getSubmitIcon(),
            ),
        );
    }

    private function getStandAloneFooter(
        ?string $action = null,
    ): FooterProvider {
        $label = $this->formValues['label'] ?? 'Item';
        $cancelRoute = $this->formValues['cancel_route'] ?? '#';
        return new FooterProvider(
            builder: $this->builder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forStandalone(
                action: $action,
                cancelRoute: $cancelRoute,
                formId: $this->config->getFormId(),
                footerClass: [
                    'modal-footer',
                    'confirm-deletion__footer',
                ],
                // ConfirmDeletionForm::make()
                submitButtonConfig: new ButtonConfig(
                    type: 'submit',
                    ariaLabel: 'Delete ' . $label,
                    label: 'Delete ' . $label,
                    style: 'danger',
                    iconConfig: new IconConfig(
                        icon: 'icon-trash',
                        ariaLabel: 'Delete ' . $label,
                    ),
                    attributes: [
                        'form' => $this->config->getFormId(),
                        'data-js-type' => 'button',
                    ],
                ),
            ),
        );
    }

    private function hiddenFieldMapping(): array
    {
        $hiddenFields = $this->config->getHiddenFields();
        if (empty($hiddenFields)) {
            return [];
        }
        $mapping = [];
        foreach ($hiddenFields as $field) {
            if ($field instanceof FormFieldConfig) {
                $field = $field->toArray();
            }
            $this->extractFieldToMapping($field, $mapping);
        }
        return $mapping;
    }
}