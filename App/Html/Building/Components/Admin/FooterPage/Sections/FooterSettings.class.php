<?php

declare(strict_types=1);

class FooterSettings extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private ButtonBuilder $buttonBuilder,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('form-card')->add(
            $html->tag('h2')->content('Footer Settings'),
            ...$this->settingsGroup(),
        );
    }

    #[Override]
    public function getKey(): string
    {
        return FooterSectionKeys::SETTINGS->value;
    }

    // ─── Private Methods ───────────────────────────────────────

    /**
     * Build all settings groups.
     */
    private function settingsGroup(): array
    {
        $html = $this->htmlBuilder;

        return [
            $html->div()->class('settings-group')->add(...$this->cacheManagement()),
            $html->div()->class('settings-group')->add(...$this->livePreview()),
            $html->div()->class('settings-group')->add(...$this->exportConfiguration()),
        ];
    }

    private function cacheManagement(): array
    {
        $html = $this->htmlBuilder;

        $button = $this->buttonBuilder
            ->add(
                type: 'button',
                buttonSize: 'md-compact',
                label: 'Clear Footer Cache',
                buttonStyle: 'secondary',
                id: 'clear-cache-btn',
                icon: 'icon-refresh',
                iconPosition: 'left',
                ariaLabel: 'Clear footer cache',
            )
            ->build();

        return [
            $html->tag('h3')->content('Cache Management'),
            $html->div()->class('form-group')->add(
                $button,
                $html->tag('small')->class('helper-text')->content('Clear cached footer data to see recent changes immediately'),
            ),
        ];
    }

    private function livePreview(): array
    {
        $html = $this->htmlBuilder;

        $buttons = $this->buttonBuilder
            ->add(
                type: 'button',
                buttonSize: 'md-compact',
                label: 'Live Preview',
                buttonStyle: 'secondary',
                id: 'preview-btn',
                icon: 'icon-eye',
                iconPosition: 'left',
                ariaLabel: 'Open live preview',
            )
            ->add(
                type: 'button',
                buttonSize: 'md-compact',
                label: 'Publish to Production',
                buttonStyle: 'primary',
                id: 'publish-btn',
                icon: 'icon-publish',
                iconPosition: 'left',
                ariaLabel: 'Publish to production',
            )
            ->build();

        return [
            $html->tag('h3')->content('Live Preview'),
            $html->div()->class('form-group')->add(
                ...(is_array($buttons) ? $buttons : [$buttons]),
            ),
        ];
    }

    private function exportConfiguration(): array
    {
        $html = $this->htmlBuilder;

        $exportButton = $this->buttonBuilder
            ->add(
                type: 'button',
                buttonSize: 'md-compact',
                label: 'Export Configuration',
                buttonStyle: 'secondary',
                id: 'export-btn',
                icon: 'icon-download',
                iconPosition: 'left',
                ariaLabel: 'Export configuration as JSON',
            )
            ->build();

        $importLabel = $html->label()
            ->class('btn', 'btn--secondary')
            ->id('import-btn')
            ->attribute('role', 'button')
            ->add(
                $html->tag('span')->class('btn__icon')->add(
                    $this->iconBuilder->createIcon('icon-upload', 'Upload Configuration'),
                ),
                $html->tag('span')->class('btn__label')->content('Import Configuration'),
                $html->input('file')
                    ->id('import-file-input')
                    ->accept('.json')
                    ->attribute('style', 'display: none;'),
            );

        return [
            $html->tag('h3')->content('Export/Import'),
            $html->div()->class('form-group')->add(
                $exportButton,
                $html->tag('small')->class('helper-text')->content('Export your footer configuration or import a previously exported one'),
            ),
            $html->div()->class('form-group')->add(
                $importLabel,
                $html->tag('small')->class('helper-text')->content('Select a JSON file to import configuration'),
            ),
        ];
    }
}