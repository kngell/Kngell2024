<?php

declare(strict_types=1);

class HeroSectionForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'heroRules',
    ];
    private const string PROVIDER_KEY = 'hero-section-form';
    private const string FORM_ID = 'hero-form-id';
    private const string FORM_NAME = 'hero-form';
    private const array FORM_CLASS = ['hero-form'];
    private const array LEFT_SECTIONS = ['basic-information', 'call-to-action'];

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

        $options = new FieldMappingPayload(
            fieldMapping: $this->sectionManager->getFieldMapping(),
        );

        $this->formValues = $formValues instanceof Entity ? $formValues->toFormArray($options) : $formValues;
        $this->fieldRenderer->setDefaultInputLayout(new FieldLayout());
        $this->files = $files;
        $this->formErrors = $formErrors;

        $htmlParts = [];
        $htmlParts[] = $html->tag('div')->class('hero__body hero-form-container')->add(
            $html->htmlBlock($this->flash->get()),
            $html->htmlBlock(
                parent::make($action, $this->formValues, $this->formErrors, $this->files),
            ),
        )->generate();

        $footer = new FooterProvider(
            builder:$this->builder,
            iconBuilder:$this->iconBuilder,
            formId:$this->getFormId(),
            footerClass:['hero__footer'],
            renderProgressBar:true,
            submitText: 'Save Hero',
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
            $form->tag('div')->class('hero-form__left')->add(...$leftComponents),
            $form->tag('div')->class('hero-form__right upload')->add(...$rightComponents),
        ];
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
            new DropzoneFieldHandler(
                $this->metadataService,
                $this->iconBuilder,
            ),
        ];
    }

    protected function getFormSections(): array
    {
        return $this->sectionManager->getSections();
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
}