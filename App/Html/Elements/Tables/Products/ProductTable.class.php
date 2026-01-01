<?php

declare(strict_types=1);

class ProductTable extends AbstractTable
{
    private const array SECTIONS_KEYS = ['caption', 'colGroup', 'thead', 'tbody'];

    /**
     * @var ProductShow[]
     */
    private array $products;

    public function __construct(array $products, private HtmlBuilder $builder, private IconBuilder $icon, private FileContentManager $file, private TypePresenterFactory $presenter)
    {
        $this->products = $products;
    }

    public function getTable(): string
    {
        $html = $this->builder;
        return $html->tag('table')->class('table')->custom([
            'summary' => 'Product list with stock, price and status information',
            'aria-describedby' => 'table-desc',
        ])->add(
            ...$this->buildTableSections(),
        )->generate();
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    protected function buildTableSections(): array
    {
        $htmlComponents = [];
        $factory = new TableSectionFactory(
            $this->products,
            $this->builder,
            $this->icon,
            $this->file,
            $this->presenter,
        );
        foreach (self::SECTIONS_KEYS as $key) {
            /** @var TableSectionInterface */
            $tableSection = $factory->create($key);
            if ($tableSection) {
                $htmlComponents[] = $tableSection->getSection();
            }
        }
        return $htmlComponents;
    }
}