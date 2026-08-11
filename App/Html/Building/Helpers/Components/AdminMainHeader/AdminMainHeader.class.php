<?php

declare(strict_types=1);

class AdminMainHeader implements StandAloneComponentInterface
{
    private ?AdminHeaderConfig $config = null;
    private bool $isEditMode = false;
    private mixed $id = null;
    private ?string $idFieldName = null;

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly Breadcrumbs $breadcrumbs,
    ) {
    }

    public function withConfig(AdminHeaderConfig $config): self
    {
        $this->config = $config;
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

    public function idFieldName(?string $name): self
    {
        $this->idFieldName = $name;
        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if ($this->config === null || $this->config->title === '') {
            return null;
        }

        $class = ['title'];
        if (is_array($params) && isset($params['class'])) {
            $class[] = $params['class'];
        }

        return $this->htmlBuilder
            ->div()
            ->class(...$class)
            ->add(
                $this->buildTitleLeft(),
                $this->config->showActions ? $this->buildTitleRight() : null,
            );
    }

    private function buildTitleLeft(): AbstractHtmlComponent
    {
        foreach ($this->config->breadcrumbs as $link) {
            $this->breadcrumbs->addLink($link);
        }

        return $this->htmlBuilder->div()->class('title-left')->add(
            $this->htmlBuilder->tag('h4')
                ->class('title-left__text')
                ->content($this->config->title),
            $this->breadcrumbs->build(),
        );
    }

    private function buildTitleRight(): ?AbstractHtmlComponent
    {
        $forms = $this->buildPrimaryActionForms();
        if ($forms === []) {
            return null;
        }

        return $this->htmlBuilder
            ->div()
            ->class('title-right')
            ->add(...$forms);
    }

    /** @return AbstractHtmlComponent[] */
    private function buildPrimaryActionForms(): array
    {
        $forms = [];
        $html = $this->htmlBuilder;

        foreach ($this->config->primaryActions as $index => $action) {
            $cfg = HeaderButtonConfig::from($action);
            if ($cfg->requiresEditMode && !$this->isEditMode) {
                continue;
            }

            $formName = $cfg->formName ?? 'header_form_' . $index;
            $csrfProtect = strtolower($cfg->method) === 'get' ? false : true;
            $form = $html->form($csrfProtect)
                ->action($cfg->action)
                ->method($cfg->method)
                ->name($formName)
                ->id($formName);

            $blockType = null;
            if ($action->blockType !== null) {
                $blockType = $html->input('hidden')
                ->name('block_type')
                ->value($action->blockType);
            }

            if ($cfg->requiresEntityId
                && $this->isEditMode
                && $this->id !== null
                && $this->idFieldName !== null
            ) {
                $form->add(
                    $html->input('hidden')
                        ->name($this->idFieldName)
                        ->value($this->id),
                    $blockType,
                );
            }

            $form->add($this->buttonBuilder->build($cfg->button));
            $forms[] = $form;
        }

        return $forms;
    }
}