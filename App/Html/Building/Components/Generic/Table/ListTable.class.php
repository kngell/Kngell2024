<?php

declare(strict_types=1);

class ListTable extends AbstractHtml implements ListTableInterface
{
    public function __construct(
        private readonly array $entities,
        private readonly TableConfig $config,
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlRegularSectionManager $sectionManager,
        private readonly SectionRenderer $sectionRenderer,
        private readonly TableSectionProvider $provider,
        private readonly FlashRenderer $flashRenderer,
    ) {
    }

    public function getHtmlElements(): string
    {
        $this->provider->registerSections($this->builder, $this->sectionManager);
        $this->sectionRenderer->tableRenderer(
            new TableRenderer($this->builder, $this->iconBuilder),
        );

        $htmlParts = [];
        $htmlParts[] = $this->builder
        ->htmlBlock($this->flashRenderer->render())
        ->generate();

        $htmlParts[] = $this->builder
            ->tag('table')
            ->class('table')
            ->custom($this->config->jsAttributes)
            ->add(...$this->buildLayout())
            ->generate();

        return implode('', $htmlParts);
    }

    /** @return AbstractHtmlComponent[] */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        $sections = $this->sectionManager->getSections($this->entities);
        $renderedSections = [];

        foreach (TableListSection::cases() as $case) {
            if (!isset($sections[$case->value])) {
                continue;
            }

            $renderedSections[] = $this->sectionRenderer->render(
                $case->value,
                $this->builder,
                $sections,
                $this,
                $this->sectionManager,
            );
        }

        return $renderedSections;
    }
}