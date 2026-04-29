<?php

declare(strict_types=1);

final class ProductForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'productRules',
    ];
    private const array SPAN_ALL_SECTIONS = ['pricing', 'inventory', 'variation', 'shipping'];
    private const string FRM_ID = 'product-frm';
    private const string FRM_NAME = 'product-frm';
    private const array FRM_CLASS = ['product__body-frm'];
    private const array NUMERIC_FIELDS = ['price', 'modifier', 'quantity'];

    public function __construct(
        HtmlBuilder $builder,
        FieldRenderer $fieldRenderer,
        FieldGroupRenderer $fieldGroupRenderer,
        DropzoneRenderer $dropzoneRenderer,
        SectionRenderer $sectionRenderer,
        ButtonBuilder $buttonBuilder,
        IconBuilder $iconBuilder,
        FieldIdGenerator $idGenerator,
        private readonly HtmlFormSectionManager $sectionManager,
        private readonly FormProgressCalculator $progressCalculator,
        private readonly SectionProviderFactory $providerFactory,
        FlashInterface $flash,
        private FileMetadataService $metadataService,
    ) {
        parent::__construct(
            $builder,
            $fieldRenderer,
            $fieldGroupRenderer,
            $dropzoneRenderer,
            $sectionRenderer,
            $buttonBuilder,
            $iconBuilder,
            $idGenerator,
            $flash,
        );
    }

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $form = $this->builder;
        $provider = $this->providerFactory->getProvider($this->getProviderKey());
        $provider->registerSections($form, $this->sectionManager);

        $options = new FieldMappingPayload(
            fieldMapping: $this->sectionManager->getFieldMapping(),
            numericFields: self::NUMERIC_FIELDS,
        );

        $this->formValues = $formValues instanceof Entity ? $formValues->toFormArray($options) : $formValues;
        // dd($this->formValues);
        $this->files = $files;
        $this->formErrors = $formErrors;
        // dd($this->formValues);
        $this->fieldRenderer->setDefaultInputLayout(new DefaultInputLayout());
        $formHtml = $form->tag('div')->class('product__body')->add(
            $form->htmlBlock($this->flash->get()),
            $this->formNumericFields(),
            $form->htmlBlock(
                parent::make($action, $this->formValues, $this->formErrors, $this->files),
            ),
        )->generate();

        $completion = $this->progressCalculator->calculateCompletion($this->formValues);
        $footer = new FooterProvider(
            builder:$this->builder,
            buttonBuilder:$this->buttonBuilder,
            dto:FooterDTO::forInlineForm(
                formId:$this->getFormId(),
                renderProgressBar:true,
                completionPercentage: $completion,
                footerClass:['product__footer'],
            ),
        );
        $footerHtml = $footer->renderFooter();

        return $formHtml . $footerHtml;
    }

    public function getFieldSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): AbstractHtmlComponent
    {
        $sectionClass = 'frm-section ' . $sectionKey;
        $extraClass = $this->getSectionExtraClass($sectionKey);
        $sectionTitle = $this->getSectionTitle($sectionKey);

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $form->tag('h4')
                    ->class('frm-section__title' . $extraClass)
                    ->content($sectionTitle),
                $form->tag('div')
                    ->class('frm-section__body' . $extraClass)
                    ->add(...$fields),
            );
    }

    public function buildLayout(?HtmlBuilder $form = null): array
    {
        $sectionsConfig = $this->getFormSections();
        $leftSections = [];
        $rightSections = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $section = $this->sectionRenderer->render($sectionKey, $form, $sectionsConfig, $this);
            if (in_array($sectionKey, ['general-information', 'media', 'pricing', 'inventory', 'variation', 'shipping'])) {
                $leftSections[] = $section;
            } else {
                $rightSections[] = $section;
            }
        }

        return [
            $form->tag('div')->class('product__body-frm--left')->add(...$leftSections),
            $form->tag('div')->class('product__body-frm--right')->add(...$rightSections),
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputBoxHandler(),
            new TextareaBoxHandler(),
            new NativeSelectBoxHandler(),
            new DropzoneFieldHandler($this->metadataService, $this->iconBuilder),
            new CurrencyFieldHandler(),
            new FieldGroupFieldHandler($this->fieldGroupRenderer),
            new ButtonFieldHandler($this->buttonBuilder),
        ];
    }

    protected function getProviderKey(): string
    {
        return 'product_form';
    }

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
    }

    protected function getFormSections(): array
    {
        return $this->sectionManager->getSections();
    }

    protected function getFormId(): string
    {
        return self::FRM_ID;
    }

    protected function getFormName(): string
    {
        return self::FRM_NAME;
    }

    protected function getFormClass(): array
    {
        return self::FRM_CLASS;
    }

    protected function formNumericFields(): AbstractHtmlComponent
    {
        $form = $this->builder;
        return $form->input('hidden')->id(InputBox::NUMERIC_FIELDS_ID)->value(json_encode(self::NUMERIC_FIELDS));
    }

    protected function getSpanAllSections(): array
    {
        return self::SPAN_ALL_SECTIONS;
    }

    protected function createTagPreviewComponent(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('tag-preview')
            ->add(
                $this->createTagButton($form, 'Watch'),
                $this->createTagButton($form, 'Gadget'),
            );
    }

    protected function createTagButton(FormBuilder $form, string $tagName): AbstractHtmlComponent
    {
        return $form->button()
            ->type('button')
            ->class('btn', 'btn--secondary', 'btn--md-tags', 'btn--icon-right')
            ->custom([
                'data-tag' => $tagName,
                'data-action' => 'remove-tag',
            ])->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon('icon-cancel', "Remove $tagName"),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content($tagName),
            );
    }
}