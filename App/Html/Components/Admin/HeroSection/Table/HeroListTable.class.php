<?php

declare(strict_types=1);

class HeroListTable extends AbstractHtml implements ListTableInterface
{
    /** @var Hero[] */
    private readonly array $heroes;

    public function __construct(
        array $heroes,
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlRegularSectionManager $sectionManager,
        private readonly SectionRenderer $sectionRenderer,
        private readonly FlashInterface $flash,
        private readonly HeroTableSectionProvider $provider,
    ) {
        $this->heroes = $heroes;
    }

    public function getTable(): string
    {
        $this->provider->registerSections($this->builder, $this->sectionManager);
        $this->sectionRenderer->tableRenderer(
            new TableRenderer($this->builder, $this->iconBuilder),
        );
        $htmlParts = [];
        $htmlParts[] = $this->builder
            ->htmlBlock($this->flash->get())
            ->generate();

        $htmlParts[] = $this->builder
            ->tag('table')
            ->class('table')
            ->attribute('data-table-type', 'hero')
            ->add(...$this->buildLayout())
            ->generate();

        return implode('', $htmlParts);
    }

    /** @return AbstractHtmlComponent[] */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        $sections = $this->sectionManager->getSections($this->heroes);
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