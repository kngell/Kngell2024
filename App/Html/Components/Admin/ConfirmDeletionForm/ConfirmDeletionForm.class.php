<?php

declare(strict_types=1);

class ConfirmDeletionForm extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'confirmDeletionRules',
        'data-ajax-form' => '',
    ];
    private const string FRM_ID = 'confirm-deletion-frm';
    private const string FRM_NAME = 'confirm-deletion-frm';
    private const array FRM_CLASS = [
        'modal-body',
        'confirm-deletion__body',
        'confirm-deletion-frm',
    ];

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
        private readonly ConfirmDeletionSectionProvider $provider,
    ) {
        parent::__construct(
            builder: $builder,
            fieldRenderer: $fieldRenderer,
            fieldGroupRenderer: $fieldGroupRenderer,
            sectionRenderer: $sectionRenderer,
            buttonBuilder: $buttonBuilder,
            iconBuilder: $iconBuilder,
            idGenerator: $idGenerator,
            flash: $flash,
        );
    }

    public function make(
        string $action = '',
        array|Entity $formValues = [],
        array $formErrors = [],
        array $files = [],
    ): string {
        $form = $this->builder;
        $this->provider->registerSections($form, $this->sectionManager);

        $label = $formValues['label'] ?? 'Item';
        $cancelRoute = $formValues['cancel_route'] ?? '#';

        $footer = new FooterProvider(
            builder: $this->builder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forStandalone(
                action: $action,
                cancelRoute: $cancelRoute,
                formId: $this->getFormId(),
                footerClass: [
                    'modal-footer',
                    'confirm-deletion__footer',
                ],
                // ConfirmDeletionForm::make()
                submitButtonConfig: new ButtonConfig(
                    type: 'submit',
                    label: 'Delete ' . $label,
                    ariaLabel: 'Delete ' . $label,
                    style: 'danger',
                    icon: 'icon-trash',
                    attributes: [
                        'form' => $this->getFormId(),
                        'data-js-type' => 'button',
                    ],
                ),
            ),
        );

        $this->fieldRenderer->setDefaultInputLayout(
            new FieldCheckboxLayout(),
        );

        return parent::make($action, $formValues, $formErrors, $files)
            . $footer->renderFooter();
    }

    public function getFieldSectionLayout(
        array $fields,
        string $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        if (count($fields) === 1) {
            return $form->div()->class('checkbox-section')->add($fields[0]);
        }

        return $form->tag('div');
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(?HtmlBuilder $form = null): array
    {
        $sectionsConfig = $this->getFormSections();
        $sections = [];

        $entityId = $this->formValues['id'] ?? null;
        if ($entityId !== null) {
            $sections[] = $form->input('hidden')
                ->name('id')
                ->value($entityId);
        }

        $sections[] = $form->input('hidden')
            ->name('confirmed')
            ->value($this->formValues['confirmed'] ?? '1');

        $sections[] = $form->htmlBlock($this->flash->get());

        foreach ($sectionsConfig as $sectionKey => $section) {
            if ($section instanceof AbstractHtmlComponent) {
                $sections[] = $section;
            } else {
                $sections[] = $this->sectionRenderer->render(
                    $sectionKey,
                    $form,
                    $sectionsConfig,
                    $this,
                );
            }
        }

        return $sections;
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
        ];
    }

    protected function getProviderKey(): string
    {
        return 'confirm_deletion';
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
}