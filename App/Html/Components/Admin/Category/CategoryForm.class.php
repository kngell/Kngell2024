<?php

declare(strict_types=1);

class CategoryForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'categoryRules',
    ];
    private const string PROVIDER_KEY = 'category-form';
    private const string FORM_ID = 'category-form-id';
    private const string FORM_NAME = 'category-form';
    private const array FORM_CLASS = ['category-form'];
    private const array FORM_SECTIONS = [
        'infos-left' => ['basic-information', 'social-media', 'open-graph'],
        'infos-right' => ['category-media', 'og-image', 'canonical-infos'],
        'content-left' => ['content-area'],
        'content-right' => ['content-style'],
        'settings-left' => ['price-range'],
        'settings-right' => ['navigation-infos'],
    ];
    private const array TAB_CONFIG = [
        'tab-category-infos' => [
            'title' => 'Category Informations',
            'state' => 'default',
            'sections' => ['infos-left', 'infos-right'],
            'contentClass' => 'category-form__content--infos',
        ],
        'tab-content-style' => [
            'title' => 'Content and style',
            'state' => null,
            'sections' => ['content-left', 'content-right'],
            'contentClass' => 'category-form__content--content',
        ],
        'tab-price' => [
            'title' => 'Price and settings',
            'state' => null,
            'sections' => ['settings-left', 'settings-right'],
            'contentClass' => 'category-form__content--settings',
        ],
        // 'tab-advanced' => [
        //     'title' => 'Advanced (Disabled)',
        //     'state' => 'disabled',
        //     'sections' => [],
        //     'contentClass' => 'category-form__content--advanced',
        // ],
    ];
    private const array NUMERIC_FIELDS = ['min_price', 'max_price'];

    public function __construct(
        private readonly CategoryFormSectionProvider $provider,
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
            buttonBuilder: new ButtonBuilder($builder, $iconBuilder),
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
        $this->provider->registerSections($this->builder, $this->sectionManager);

        $options = new FieldMappingPayload(
            fieldMapping: $this->sectionManager->getFieldMapping($formValues),
            numericFields: self::NUMERIC_FIELDS,
        );

        $this->formValues = $formValues instanceof Entity ? $formValues->toFormArray($options) : $formValues;
        // dd($options, $formValues, $this->formValues);
        $this->registerFieldLayouts();
        // $this->fieldRenderer->setDefaultInputLayout(new FieldLayout());
        $this->files = $files;
        $this->formErrors = $formErrors;

        $htmlParts = [];
        $htmlParts[] = $html->tag('div')->class('category__body')->add(
            $html->htmlBlock($this->flash->get()),
            $html->htmlBlock(
                parent::make($action, $this->formValues, $this->formErrors, $this->files),
            ),
        )->generate();

        $footer = new FooterProvider(
            builder:$this->builder,
            buttonBuilder:$this->buttonBuilder,
            dto: FooterDTO::forInlineForm(
                formId:$this->getFormId(),
                footerClass:['category__footer'],
                renderProgressBar:true,
                submitText: 'Save',
                submitIcon:'icon-save',
            ),
        );
        $htmlParts[] = $footer->renderFooter();

        return implode(' ', $htmlParts);
    }

    /**
     * @param HtmlBuilder $form
     *
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(?HtmlBuilder $form = null): array
    {
        $sectionsConfig = $this->getFormSections();
        $formSections = $this->buildFormSections($sectionsConfig);

        // Build tabs dynamically
        $tabs = new FormTabs($form);
        $tabs->setTabClass(['category-form__tabs']);

        $contentChildren = [];

        foreach (self::TAB_CONFIG as $tabId => $config) {
            $tabs->addTab($config['title'], $tabId, $config['state']);

            // Build content for this tab
            $tabContent = $form->tag('div')->class('tab-content', $config['contentClass']);

            foreach ($config['sections'] as $sectionGroup) {
                if (isset($formSections[$sectionGroup])) {
                    $wrapperClass = str_contains($sectionGroup, 'right') ? 'category-form__right' : 'category-form__left';
                    $tabContent->add(
                        $form->tag('div')->class($wrapperClass)->add(...$formSections[$sectionGroup]),
                    );
                }
            }

            $contentChildren[] = $tabContent;
        }

        $contentLayout = $form->tag('div')->class('category-form__content')->add(...$contentChildren);

        return $tabs->getComponents($contentLayout);
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
    // public function getDefaultInputLayoutName(): ?string
    // {
    //     return 'modern'; // This form uses modern layout
    // }

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new CustomSelectFieldHandler(),
            new TextareaFieldHandler(),
            new DropzoneFieldHandler(
                $this->metadataService,
                $this->iconBuilder,
            ),
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

    private function buildFormSections(array $sectionsConfig): array
    {
        $formSections = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $result = $this->sectionRenderer->render(
                $sectionKey,
                $this->builder,
                $sectionsConfig,
                $this,
                $this->sectionManager,
            );

            $components = is_array($result) ? $result : [$result];
            $formSections = $this->getFormSectionElement($sectionKey, $components, $formSections);
        }

        return $formSections;
    }

    private function getFormSectionElement(string $sectionKey, array $component, array $formSections): array
    {
        foreach (self::FORM_SECTIONS as $keyGroup => $sectionConfig) {
            if (in_array($sectionKey, $sectionConfig)) {
                $formSections[$keyGroup][] = reset($component);
                break;
            }
        }
        return $formSections;
    }
}