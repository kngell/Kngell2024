<?php

declare(strict_types=1);

class ProductListTable extends AbstractHtml implements ListTableInterface
{
    /**
     * @var ProductShow[]
     */
    private array $products;

    public function __construct(
        array $products,
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlRegularSectionManager $sectionManager,
        private readonly SectionRenderer $sectionRenderer,
        private FlashInterface $flash,
        private ProductTableSectionProvider $provider,
    ) {
        $this->products = $products;
    }

    public function getTable(): string
    {
        $html = $this->builder;

        $this->provider->registerSections($this->builder, $this->sectionManager);
        $this->sectionRenderer->tableRenderer(
            new TableRenderer($html, $this->iconBuilder),
        );
        return $html->htmlBlock($this->flash->get())->generate() . $html->tag('table')->class('table')->attribute('data-table-type', 'products')->add(
            ...$this->buildLayout(),
        )->generate();
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        $allSections = [];
        $sections = $this->sectionManager->getSections($this->products);
        foreach (TableListSection::cases() as $case) {
            if (isset($sections[$case->value])) {
                $allSections[] = $this->sectionRenderer->render(
                    $case->value,
                    $this->builder,
                    $sections,
                    $this,
                    $this->sectionManager,
                );
            }
        }
        return $allSections;
    }
}