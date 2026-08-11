<?php

declare(strict_types=1);

class HtmlPageElement extends AbstractForm implements PageTemplateInterface
{
    private ?PageLayoutInterface $layoutBuilder;

    public function __construct(
        PageConfig $config,
        private readonly PageSectionProvider $provider,
        HtmlRegularSectionManager $sectionManager,
        HtmlBuilder $builder,
        ButtonBuilder $buttonBuilder,
        IconBuilder $iconBuilder,
        FlashRenderer $flashRenderer,
        FileMetadataService $metadataService,
        private array $entities,
        private array $pagination = [],
    ) {
        $this->layoutBuilder = $config->getLayoutBuilder();
        parent::__construct(
            config: $config,
            builder: $builder,
            buttonBuilder: $buttonBuilder,
            iconBuilder: $iconBuilder,
            flashRenderer: $flashRenderer,
            metadataService: $metadataService,
            sectionManager: $sectionManager,
        );
    }

    public function getHtmlElements(): ?string
    {
        $htmlParts = [$this->flashRenderer->render()];
        $this->provider->registerSections($this->builder, $this->sectionManager);

        $rendered = implode('', $this->render());
        if ($this->config->hasContainer()) {
            $containerClass = $this->config->getContainerClass();
            $rendered = $this->builder->div()->class(...$containerClass)->add(
                $this->builder->htmlBlock($rendered),
            )->generate();
        }
        $htmlParts[] = $rendered;
        return implode('', $htmlParts);
    }

    /**
     * @param null|HtmlBuilder $html
     *
     * @return array<string, AbstractHtmlComponent|list<AbstractHtmlComponent>>
     */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        return $this->layoutBuilder->buildLayout(
            sectionManager: $this->sectionManager,
            builder: $html,
            htmlInstance: $this,
            config: $this->config,
            entities: $this->entities,
            pagination: $this->pagination,
        );
    }

    public function render(): array
    {
        $layout = $this->buildLayout($this->builder);

        if (empty($layout)) {
            return [];
        }

        $html = [];

        foreach ($layout as $key => $section) {
            if ($section instanceof AbstractHtmlComponent) {
                $html[$key] = $section->generate();
            } elseif (is_array($section)) {
                $html[$key] = [];
                foreach ($section as $subSection) {
                    if ($subSection instanceof AbstractHtmlComponent) {
                        $html[$key][] = $subSection->generate();
                    }
                }
                if (count($html[$key]) === 1) {
                    $html[$key] = $html[$key][0];
                } else {
                    $html[$key] = implode('', $html[$key]);
                }
            }
        }

        return $html;
    }
}