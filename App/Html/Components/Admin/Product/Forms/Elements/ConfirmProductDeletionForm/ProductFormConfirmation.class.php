<?php

declare(strict_types=1);

class ProductFormConfirmation extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'productDeletionRules',
    ];
    private const string FRM_ID = 'confirm-deletion-frm';
    private const string FRM_NAME = 'confirm-deletion-frm';
    private const array FRM_CLASS = ['modal-body', 'confirm-deletion__body', 'confirm-deletion-frm'];

    public function __construct(
        HtmlBuilder $builder,
        FieldRenderer $fieldRenderer,
        FieldGroupRenderer $fieldGroupRenderer,
        SectionRenderer $sectionRenderer,
        ButtonBuilder $buttonBuilder,
        IconBuilder $iconBuilder,
        FieldIdGenerator $idGenerator,
        FlashInterface $flash,
        private readonly HtmlFormSectionManager $sectionManager,
        private readonly SectionProviderFactory $providerFactory,
    ) {
        parent::__construct(
            builder:$builder,
            fieldRenderer:$fieldRenderer,
            fieldGroupRenderer:$fieldGroupRenderer,
            sectionRenderer:$sectionRenderer,
            buttonBuilder:$buttonBuilder,
            iconBuilder:$iconBuilder,
            idGenerator:$idGenerator,
            flash:$flash,
        );
    }

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $form = $this->builder;
        $provider = $this->providerFactory->getProvider($this->getProviderKey());
        $provider->registerSections($form, $this->sectionManager);

        $footer = new FooterProvider(
            builder:$this->builder,
            iconBuilder:$this->iconBuilder,
            formId:$this->getFormId(),
            footerClass:['modal-footer', 'confirm-deletion__footer'],
            submitText: 'Delete Product',
            submitIcon: 'icon-trash',
            submitClass: ['btn', 'btn--danger', 'btn--md-compact', 'btn--icon-left'],
        );
        $this->fieldRenderer->setDefaultInputLayout(new DefaultInputLayout());
        return parent::make($action, $formValues, $formErrors, $files) . $footer->renderFooter();
    }

    public function getFieldSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        if (count($fields) === 1) {
            return $fields[0];
        }
        return $form->tag('div');
    }

    /**
     * @param HtmlBuilder $form
     *
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(HtmlBuilder $form): array
    {
        $sectionsConfig = $this->getFormSections();
        $productId = $this->formValues['product_id'] ?? null;
        $sections = [];
        if ($productId !== null) {
            $sections[] = $form->input('hidden')->name('product_id')->value($productId);
        }
        $section[] = $form->htmlBlock($this->flash->get());

        foreach ($sectionsConfig as $sectionKey => $section) {
            if ($section instanceof AbstractHtmlComponent) {
                $sections[] = $section;
            } else {
                $sections[] = $this->sectionRenderer->render($sectionKey, $form, $sectionsConfig, $this);
            }
        }

        return $sections;
    }

    protected function getFieldHandlers(): array
    {
        return [];
    }

    protected function getProviderKey(): string
    {
        return 'product_confirm_deletion';
    }

    protected function getFormSections(): array
    {
        return $this->sectionManager->getSections($this->formValues);
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

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
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
        $textButton = 'Delete Product Permanently';
        $icon = 'icon-trash';

        return $form->button()
            ->type('submit')
            ->class('btn', 'btn--primary', 'btn--md-compact', 'btn--icon-left')
            ->id('deleteButton')
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon($form, $icon, $textButton),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content($textButton),
            );
    }

    private function safetyNote(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class('text-center')->add(
            $form->tag('small')->class('text-muted')->add(
                $form->text('For security, this action is logged and requires confirmation.'),
            ),
        );
    }
}