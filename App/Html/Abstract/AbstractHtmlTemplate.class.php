<?php

declare(strict_types=1);

abstract readonly class AbstractHtmlTemplate
{
    public function __construct(
        protected HtmlBuilder $builder,
        protected SectionRenderer $sectionRenderer,
        protected IconBuilder $iconBuilder,
        protected ButtonBuilder $buttonBuilder,
        protected FlashInterface $flash,
    ) {
    }

    /**
     * Builds the full HTML layout for the page.
     */
    public function make(array $data = []): string
    {
        $sections = $this->buildLayout($this->builder, $data);
        $container = $this->builder
            ->tag('div')
            ->class($this->getContainerClass())
            ->add(...$sections);

        return $container->generate();
    }

    /**
     * Child classes must define page sections.
     */
    abstract protected function buildLayout(HtmlBuilder $html, array $data): array;

    /**
     * Default container class, can be overridden.
     */
    protected function getContainerClass(): string
    {
        return 'html-page';
    }

    /**
     * Helper: render a flash message block.
     */
    protected function renderFlash(): string
    {
        $message = $this->flash->get();
        return $message
            ? $this->builder->tag('div')->class('flash-message')->content($message)->generate()
            : '';
    }

    /**
     * Helper: icon + text block.
     */
    protected function renderIconText(string $icon, string $label): string
    {
        $form = $this->builder->form();
        return $this->builder->tag('div')
            ->class('icon-text')
            ->add(
                $this->iconBuilder->createIcon($form, $icon, $label),
                $this->builder->tag('span')->content($label),
            )
            ->generate();
    }

    /**
     * Optional helper for reusable sections.
     */
    protected function renderSection(string $title, string $content): string
    {
        return $this->builder->tag('section')
            ->class('html-section')
            ->add(
                $this->builder->tag('h3')->content($title),
                $this->builder->tag('div')->class('html-section__content')->content($content),
            )
            ->generate();
    }
}