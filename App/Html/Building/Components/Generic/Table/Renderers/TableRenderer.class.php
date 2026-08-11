<?php

declare(strict_types=1);

final class TableRenderer
{
    /** @var array<string, TableSectionRendererInterface> */
    private array $renderers = [];

    public function __construct(
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $icon,
    ) {
        $this->registerRenderers();
    }

    public function register(TableListSection $section, TableSectionRendererInterface $renderer): self
    {
        $this->renderers[$section->value] = $renderer;
        return $this;
    }

    /**
     * @param TableSectionInterface $section  The section object (has entities + config)
     * @param mixed                 $config   The config from getConfig() (column definitions)
     */
    public function render(
        TableSectionInterface $section,
        mixed $config,
    ): AbstractHtmlComponent {
        $sectionType = $section->getTableSectionType();
        $renderer = $this->renderers[$sectionType->value] ?? null;

        if ($renderer === null) {
            throw new LogicException(
                sprintf(
                    'No renderer registered for table section "%s".',
                    $sectionType->value,
                ),
            );
        }

        // Package config + entities for body renderer
        $renderData = match ($sectionType) {
            TableListSection::TBODY => [
                'columns' => $config,
                'entities' => $section->getContext(),
            ],
            default => $config,
        };

        return $renderer->render($renderData, $this->builder);
    }

    public function supports(TableListSection $sectionType): bool
    {
        return isset($this->renderers[$sectionType->value]);
    }

    private function registerRenderers(): void
    {
        $this->renderers = [
            TableListSection::TBODY->value => new TableBodyRenderer($this->icon),
            TableListSection::THEAD->value => new TableHeadRenderer($this->builder, $this->icon),
        ];
    }
}