<?php

declare(strict_types=1);

final class TableSectionFactory
{
    /** @var ProductTableSectionInterface[] */
    private array $tableSections = [];

    /**
     * @var ProductShow[]
     */
    private array $products;

    public function __construct(array $products, private HtmlBuilder $builder, private IconBuilder $icon, private FileContentManager $file, private HtmlSectionPresentationService $presenter)
    {
        $this->products = $products;
        $this->registerTableSection();
    }

    public function create(string $key): ProductTableSectionInterface
    {
        try {
            foreach ($this->tableSections as $section) {
                if ($section->supports($key)) {
                    return $section;
                }
            }
            throw new RuntimeException('Section not defined');
        } catch (Throwable $e) {
            throw new TableSectionException('Unable to create a section', $e->getCode());
        }
    }

    private function registerTableSection(): void
    {
        $this->tableSections[] = new TableCaptionSection($this->builder, $this->icon, $this->presenter);
        $this->tableSections[] = new TableColGroupSection($this->builder, $this->icon, $this->presenter);
        $this->tableSections[] = new TableHeadSection($this->builder, $this->icon, $this->file, $this->presenter);
        $this->tableSections[] = new TableBodySection($this->builder, $this->icon, $this->products, $this->presenter);
    }
}