<?php

declare(strict_types=1);

class SmallBannerForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'smallBannerRules',
    ];
    private const string PROVIDER_KEY = 'small-banner-form';
    private const string FORM_ID = 'small-banner-form';
    private const string FORM_NAME = 'small-banner-frm';
    private const array FORM_CLASS = ['small-banner-frm'];
    private const array LEFT_SECTIONS = ['core-configuration', 'product-relationship', 'custom-override'];

    public function __construct(
        private readonly SectionProviderFactory $providerFactory,
        private readonly HtmlFormSectionManager $sectionManager,
        private FileMetadataService $metadataService,
        HtmlBuilder $builder,
        FieldRenderer $fieldRenderer,
        DropzoneRenderer $dropzoneRenderer,
        SectionRenderer $sectionRenderer,
        FieldGroupRenderer $fieldGroupRenderer,
        FieldIdGenerator $idGenerator,
        FlashInterface $flash,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct(
            builder: $builder,
            fieldRenderer: $fieldRenderer,
            sectionRenderer: $sectionRenderer,
            fieldGroupRenderer: $fieldGroupRenderer,
            dropzoneRenderer: $dropzoneRenderer,
            idGenerator: $idGenerator,
            flash: $flash,
            iconBuilder: $iconBuilder,
        );
    }

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $html = $this->builder;
        $provider = $this->providerFactory->getProvider($this->getProviderKey());
        $provider->registerSections($this->builder, $this->sectionManager);

        $mapping = new FieldMappingPayload(
            fieldMapping: $this->sectionManager->getFieldMapping(),
        );

        $this->formValues = $formValues instanceof Entity ? $formValues->toFormArray($mapping) : $formValues;

        $this->registerFieldLayouts();

        $this->files = $files;
        $this->formErrors = $formErrors;

        $htmlParts = [];
        $htmlParts[] = $html->tag('div')->class('small-banner__body')->add(
            $html->htmlBlock($this->flash->get()),
            $html->htmlBlock(
                parent::make($action, $this->formValues, $this->formErrors, $this->files),
            ),
        )->generate();

        $footer = new FooterProvider(
            builder:$this->builder,
            iconBuilder:$this->iconBuilder,
            formId:$this->getFormId(),
            footerClass:['small-banner__footer'],
            renderProgressBar:true,
            submitText: 'Save',
            submitIcon:'icon-save',
        );
        $htmlParts[] = $footer->renderFooter();

        return implode(' ', $htmlParts);
    }

    public function buildLayout(HtmlBuilder $form): array
    {
        $sectionsConfig = $this->getFormSections();
        $leftComponents = [];
        $rightComponents = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $result = $this->sectionRenderer->render(
                $sectionKey,
                $form,
                $sectionsConfig,
                $this,
                $this->sectionManager,
            );

            $components = is_array($result) ? $result : [$result];

            // Add to appropriate column
            if (in_array($sectionKey, self::LEFT_SECTIONS)) {
                $leftComponents = array_merge($leftComponents, $components);
            } else {
                $rightComponents = array_merge($rightComponents, $components);
            }
        }

        return [
            $form->tag('div')->class('small-banner-frm__left')->add(...$leftComponents),
            $form->tag('div')->class('small-banner-frm__right', 'media')->add(...$rightComponents),
        ];
    }

    public function getInputLayoutNameForField(array $field): ?string
    {
        if (($field['type'] === 'custom-select')) {
            return 'custom-select';
        }

        // Use input layout for all other input types
        if (in_array($field['type'], ['text', 'textarea', 'email', 'number', 'url', 'search', 'select'])) {
            return 'input';
        }
        return null;
    }

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new DropzoneFieldHandler(
                $this->metadataService,
                $this->iconBuilder,
            ),
            new NativeSelectFieldHandler(),
            new CustomSelectFieldHandler(),
            new TextareaFieldHandler(),
        ];
    }

    protected function getFormSections(): array
    {
        return $this->sectionManager->getSections($this->formValues);
    }

    protected function getFormId(): string
    {
        return self::FORM_ID;
    }

    protected function getFormName(): string
    {
        return self::FORM_NAME;
    }

    protected function getFormClass(): array
    {
        return self::FORM_CLASS;
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }

    private function registerFieldLayouts(): void
    {
        $this->fieldRenderer->registerNamedLayout('custom-select', new CustomSelectLayout());
        $this->fieldRenderer->registerNamedLayout('input', new FieldLayout());
    }
}