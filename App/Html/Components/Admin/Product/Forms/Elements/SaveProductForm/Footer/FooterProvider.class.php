<?php

declare(strict_types=1);

class FooterProvider
{
    private const array FOOTER_CLASS = ['buttons-group'];

    private AbstractHtmlComponent $footer;

    public function __construct(
        private HtmlBuilder $builder,
        private IconBuilder $iconBuilder,
        private ?string $formId = null,
        private array $footerClass = ['product__footer'],
        private bool $renderProgressBar = false,
        private int $completionPercentage = 0,
        private string $submitText = 'Add Product',
        private string $submitIcon = 'icon-plus',
        private array $submitClass = [],
    ) {
        $this->buildFooter($completionPercentage);
    }

    public function renderFooter(): string
    {
        return $this->footer->generate();
    }

    /**
     * @return AbstractHtmlComponent
     */
    public function getFooter(): AbstractHtmlComponent
    {
        return $this->footer;
    }

    private function buildFooter(): void
    {
        $form = $this->builder->form();
        $footerClass = array_merge($this->footerClass, self::FOOTER_CLASS);
        $this->footer = $form->tag('div')
            ->class(...$footerClass)
            ->add(
                $this->renderProgressBar ?
                $this->renderCompletionProgress($this->completionPercentage, $form) : null,
                $this->renderActionButtons($form),
            );
    }

    private function renderCompletionProgress(int $percentage, FormBuilder $form): AbstractHtmlComponent
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

    private function renderActionButtons(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('buttons')
            ->add(
                $this->createCancelButton($form),
                $this->createSubmitButton($form),
            );
    }

    private function createCancelButton(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->button()
            ->type('button')
            ->class('btn', 'btn--outlined', 'btn--md-compact', 'btn--icon-left')
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->iconBuilder->createIcon($form, 'icon-cancel', 'Cancel'),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content('Cancel'),
            );
    }

    private function createSubmitButton(FormBuilder $form): AbstractHtmlComponent
    {
        $submitClass = $this->submitClass;
        if (empty($submitClass)) {
            $submitClass = ['btn', 'btn--primary', 'btn--md-compact', 'btn--icon-left'];
        }
        $button = $form->button()
            ->type('submit')
            ->class(...$submitClass);
        if ($this->formId !== null) {
            $button->custom(['form' => $this->formId]);
        }
        return $button->add(
            $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->iconBuilder->createIcon($form, $this->submitIcon, $this->submitText),
                    ),
            $form->tag('span')
                    ->class('btn__label')
                    ->content($this->submitText),
        );
    }
}