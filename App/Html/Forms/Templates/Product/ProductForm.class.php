<?php

declare(strict_types=1);

final readonly class ProductForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'productRules',
    ];

    private const array SPAN_ALL_SECTIONS = ['pricing', 'inventory', 'variation', 'shipping'];
    private const string FRM_ID = 'product-frm';
    private const string FRM_NAME = 'product-frm';
    private const string FRM_CLASS = 'product__body-frm';

    public function __construct(
        HtmlBuilder $builder,
        FieldRenderer $fieldRenderer,
        FieldGroupRenderer $fieldGroupRenderer,
        SectionRenderer $sectionRenderer,
        ButtonBuilder $buttonBuilder,
        IconBuilder $iconBuilder,
        FieldIdGenerator $idGenerator,
        private readonly FormSectionManager $sectionManager,
        private readonly FormProgressCalculator $progressCalculator,
        private readonly ProductSectionServiceProvider $provider,
        private readonly FlashInterface $flash,
    ) {
        parent::__construct(
            $builder,
            $fieldRenderer,
            $fieldGroupRenderer,
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
        $this->provider->registerSections($this->sectionManager, $form);
        $formHtml = $form->tag('div')->class('product__body')->add(
            $form->htmlBlock($this->flash->get()),
            $form->htmlBlock(
                parent::make($action, $formValues, $formErrors, $files),
            ),
        )->generate();

        $completion = $this->progressCalculator->calculateCompletion($formValues);
        $footerHtml = $this->renderFooter($completion);

        return $formHtml . $footerHtml;
    }

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
    }

    protected function getFormSections(): array
    {
        return $this->sectionManager->getFormSections([]);
    }

    protected function getFormId(): string
    {
        return self::FRM_ID;
    }

    protected function getFormName(): string
    {
        return self::FRM_NAME;
    }

    protected function getFormClass(): string
    {
        return self::FRM_CLASS;
    }

    protected function buildFormLayout(FormBuilder $form): array
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

    protected function getSpanAllSections(): array
    {
        return self::SPAN_ALL_SECTIONS;
    }

    // Footer rendering methods (same as before)
    protected function renderFooter(int $completionPercentage = 0): string
    {
        $form = $this->builder->form();

        $footer = $form->tag('div')
            ->class('product__footer', 'buttons-group')
            ->add(
                $this->renderCompletionProgress($completionPercentage, $form),
                $this->renderActionButtons($form),
            );

        return $footer->generate();
    }

    protected function renderCompletionProgress(int $percentage, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('completeness')
            ->add(
                $form->tag('span')
                    ->class('completeness__text')
                    ->content('Product completion:'),
                $form->tag('div')
                    ->class('completeness__progress-container')
                    ->add(
                        $form->tag('div')
                            ->class('completeness-progress')
                            ->add(
                                $form->tag('div')
                                    ->class('completeness-progress--bar')
                                    ->custom(['style' => "width: {$percentage}%;"]),
                            ),
                        $form->tag('span')
                            ->class('completeness-percentage')
                            ->content("{$percentage}%"),
                    ),
            );
    }

    protected function renderActionButtons(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('buttons')
            ->add(
                $this->createCancelButton($form),
                $this->createSubmitButton($form),
            );
    }

    protected function createCancelButton(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->button()
            ->type('button')
            ->class('btn', 'btn--outlined', 'btn--md-compact', 'btn--icon-left')
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon($form, 'icon-cancel', 'Cancel'),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content('Cancel'),
            );
    }

    protected function createSubmitButton(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->button()
            ->type('submit')
            ->class('btn', 'btn--primary', 'btn--md-compact', 'btn--icon-left')
            ->custom(['form' => self::FRM_ID])
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon($form, 'icon-plus', 'Add Product'),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content('Add product'),
            );
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
                        $this->createIcon($form, 'icon-cancel', "Remove $tagName"),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content($tagName),
            );
    }
}
