<?php

declare(strict_types=1);

class AdminMainHeader implements StandAloneComponentInterface
{
    private ?string $title = null;
    private array $btnConfig = [];
    private array $linkConfig = [];
    private bool $isEditMode = false;
    private mixed $id = null;
    private ?string $idFieldName = null;

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly Breadcrumbs $breadcrumbs,
    ) {
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function withButtons(array $btnConfig): self
    {
        $this->btnConfig = $btnConfig;
        return $this;
    }

    public function withBreadcrumbs(array $linkConfig): self
    {
        $this->linkConfig = $linkConfig;
        return $this;
    }

    public function isEditMode(bool $isEditMode = true): self
    {
        $this->isEditMode = $isEditMode;
        return $this;
    }

    public function id(mixed $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function idFieldName(string $name): self
    {
        $this->idFieldName = $name;
        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if ($this->title === null) {
            return null;
        }

        $buttons = $this->buildButtons();

        return $this->htmlBuilder
            ->div()
            ->class('title')
            ->add(
                $this->buildTitleLeft($this->buildBreadcrumbs()),
                $this->buildTitleRight($buttons),
            );
    }

    private function buildBreadcrumbs(): AbstractHtmlComponent
    {
        foreach ($this->linkConfig as $link) {
            $this->breadcrumbs->addLink($link);
        }

        return $this->breadcrumbs->build();
    }

    private function buildButtons(): array
    {
        $buttons = [];
        $html = $this->htmlBuilder;

        foreach ($this->btnConfig as $index => $config) {
            $headerConfig = HeaderButtonConfig::fromArray($config);

            // Skip buttons that require edit mode when not editing
            if ($headerConfig->requiresEditMode && !$this->isEditMode) {
                continue;
            }

            $formName = $headerConfig->formName
                ?? 'header_form_' . $index;

            $form = $html->form()
                ->action($headerConfig->action)
                ->method('post')
                ->name($formName)
                ->id($formName);

            // Only attach entity ID to forms that need it
            if ($headerConfig->requiresEntityId
                && $this->isEditMode
                && $this->id !== null
                && $this->idFieldName !== null
            ) {
                $form->add(
                    $html->input('hidden')
                        ->name($this->idFieldName)
                        ->value($this->id),
                );
            }

            $form->add($this->buttonBuilder->build($headerConfig->button));
            $buttons[] = $form;
        }

        return $buttons;
    }

    private function buildTitleLeft(AbstractHtmlComponent $breadcrumbs): AbstractHtmlComponent
    {
        return $this->htmlBuilder->div()->class('title-left')->add(
            $this->htmlBuilder->tag('h4')
                ->class('title-left__text')
                ->content($this->title),
            $breadcrumbs,
        );
    }

    private function buildTitleRight(array $buttons): ?AbstractHtmlComponent
    {
        if (empty($buttons)) {
            return null;
        }

        return $this->htmlBuilder
            ->div()
            ->class('title-right')
            ->add(...$buttons);
    }
}