<?php

declare(strict_types=1);
final class FooterProvider
{
    private const array FOOTER_CLASS = ['buttons-group'];

    private AbstractHtmlComponent $footer;

    public function __construct(
        private HtmlBuilder $builder,
        private ButtonBuilder $buttonBuilder,
        private FooterDTO $dto,
    ) {
        $this->buildFooter();
    }

    public function renderFooter(): string
    {
        return $this->footer->generate();
    }

    public function getFooter(): AbstractHtmlComponent
    {
        return $this->footer;
    }

    private function buildFooter(): void
    {
        $footerClass = array_merge(
            $this->dto->footerClass,
            self::FOOTER_CLASS,
        );

        $this->footer = $this->builder->tag('div')
            ->class(...$footerClass)
            ->add(
                $this->dto->renderProgressBar
                    ? $this->renderCompletionProgress($this->dto->completionPercentage)
                    : null,
                $this->renderActionButtons(),
            );
    }

    private function renderCompletionProgress(int $percentage): AbstractHtmlComponent
    {
        $html = $this->builder;

        return $html->tag('div')
            ->class('completeness')
            ->add(
                $html->tag('span')
                    ->class('completeness__text')
                    ->content('Completion:'),
                $html->tag('div')
                    ->class('completeness__progress-container')
                    ->add(
                        $html->tag('div')
                            ->class('completeness-progress')
                            ->add(
                                $html->tag('div')
                                    ->class('completeness-progress--bar')
                                    ->custom([
                                        'style' => "width: {$percentage}%;",
                                    ]),
                            ),
                        $html->tag('span')
                            ->class('completeness-percentage')
                            ->content("{$percentage}%"),
                    ),
            );
    }

    private function renderActionButtons(): AbstractHtmlComponent
    {
        return $this->builder->tag('div')
            ->class('buttons')
            ->add(
                $this->buildCancelButton(),
                $this->buildSubmitButton(),
            );
    }

    /**
     * Cancel button strategy:
     * - Standalone (wrapWithForm): wrap in no-JS form for graceful degradation
     * - Inline: bare button, JS handles close
     */
    private function buildCancelButton(): ?AbstractHtmlComponent
    {
        $config = $this->dto->getCancelButtonConfig();
        $button = $this->buttonBuilder->build($config);

        if ($button === null) {
            return null;
        }

        if ($this->dto->wrapWithForm && $this->dto->cancelRoute !== '#') {
            return $this->wrapInNoJsForm($button, $this->dto->cancelRoute);
        }

        return $button;
    }

    /**
     * Submit button strategy:
     * - Has formId: button uses form="" attribute, no wrapper needed
     * - Standalone without formId: wrap in its own form
     * - Inline: bare button, lives inside the form already
     */
    private function buildSubmitButton(): ?AbstractHtmlComponent
    {
        $config = $this->dto->getSubmitButtonConfig();
        $button = $this->buttonBuilder->build($config);

        if ($button === null) {
            return null;
        }

        if ($this->dto->submitNeedsWrapper()) {
            return $this->wrapInForm($button, $this->dto->action);
        }

        return $button;
    }

    private function wrapInNoJsForm(
        AbstractHtmlComponent $button,
        string $action,
    ): AbstractHtmlComponent {
        return $this->builder->form()
            ->action($action)
            ->method($this->dto->method)
            ->custom(['data-nojs-only' => ''])
            ->add($button);
    }

    private function wrapInForm(
        AbstractHtmlComponent $button,
        string $action,
    ): AbstractHtmlComponent {
        return $this->builder->form()
            ->action($action)
            ->method($this->dto->method)
            ->add($button);
    }
}