<?php

declare(strict_types=1);

class TableBodySection extends AbstractTableSection
{
    private const array TABLE_COLUMNS = [
        'col1' => 'product',
        'col2' => 'sku',
        'col3' => 'category',
        'col4' => 'stock',
        'col5' => 'price',
        'col6' => 'status',
        'col7' => 'added',
        'col8' => 'action',
    ];

    /**
     * @var ProductShow[]
     */
    private array $products;

    private HtmlSectionPresentationService $presenter;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        array $products,
        HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($builder, $icon);
        $this->products = $products;
        $this->presenter = $presenter;
    }

    public function supports(string $key): bool
    {
        return $key === 'tbody';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('tbody')->class('table__body')
            ->custom(['aria-describedby' => 'table-desc'])
            ->add(...$this->tableBodyRows());
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function tableBodyRows(): array
    {
        $bodyRows = [];
        foreach ($this->products as $index => $product) {
            $row = '_row' . ($index + 1);
            $bodyRows[] = $this->tableBodyRow($product, $row);
        }
        return $bodyRows;
    }

    private function tableBodyRow(ProductShow $product, string $row): AbstractHtmlComponent
    {
        $html = $this->builder;
        $colIndex = 1;
        return $html->tag('tr')->class('table__body--row')->add(
            $html->tag('th')->custom(['scope' => 'row'])->class('table__body--row-cell')->add(
                $this->bodyCellProductStart($product, $this->id($colIndex, $row)),
            ),
            $this->normalBodyCell(
                $this->presenter->show($product, $product->getSku(), 'sku'),
                self::TABLE_COLUMNS['col1'],
            ),
            $this->normalBodyCell(
                $this->presenter->show($product, $product->getCategory()->getName(), 'category'),
                self::TABLE_COLUMNS['col2'],
            ),
            $this->normalBodyCell(
                $this->presenter->show($product, $product->getStockQuantity(), 'stockQuantity'),
                self::TABLE_COLUMNS['col3'],
            ),
            $this->normalBodyCell(
                $this->presenter->showRelated($product, 'productRegionalPrice', 'basePrice'),
                self::TABLE_COLUMNS['col4'],
            ),
            $this->badgeBodyCell(
                $this->presenter->showRelated($product, 'productStatus', 'name'),
                self::TABLE_COLUMNS['col6'],
                ['badge', 'badge--warning'],
            ),
            $this->normalBodyCell(
                $this->presenter->showField($product, 'createdAt'),
                self::TABLE_COLUMNS['col7'],
            ),
            $this->actionBodyCell($product),
        );
    }

    private function id(int &$col, string $row): string
    {
        $id = 'col_' . $col . $row;
        $col++;
        if ($col > count(self::TABLE_COLUMNS) - 1) {
            $col = 1;
        }
        return $id;
    }

    private function bodyCellProductStart(ProductShow $product, string $id): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('div')->class('body-cell-product', 'body-cell-product--checkbox')->add(
            $html->input('checkbox')->id($id)->name('products[]')->value('1'),
            $html->label()->class('body-cell-product__label')->for($id)->add(
                $this->media($product->getMainImage(), 'Main Image'),
                $html->tag('ul')->class('text-container')->add(
                    $html->tag('li')->class('text-container__name')->content($product->getName()),
                    $this->variation($product->getProductVariationShow()),
                ),
            ),
        );
    }

    private function normalBodyCell(mixed $content, string $class = ''): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('td')->class('table__body--row-cell')->add(
            $html->tag('div')->class('body-cell', $class)->add(
                $html->tag('span')->content($content),
            ),
        );
    }

    private function badgeBodyCell(mixed $content, string $class = '', array $badgeClass = []): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('td')->class('table__body--row-cell')->add(
            $html->tag('div')->class('body-cell', $class)->add(
                $html->tag('span')->add(
                    $html->tag('span')->class(...$badgeClass)->content($content),
                ),
            ),
        );
    }

    private function actionBodyCell(ProductShow $product): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('td')->class('table__body--row-cell')->add(
            $html->tag('div')->class('action-container')->add(
                $html->form()->action('product-show/index')->method('post')->class('body-cell-action', 'view-action')->add(
                    $html->input('hidden')->name('public_id')->value($this->presenter->showField($product, 'public_id')),
                    $html->button('submit')->class('icon-container')->add(
                        $this->icon->createIcon($html, 'icon-eye', 'Eye', ['eye']),
                        $html->tag('span')->class('visually-hidden')->content('View Product A'),
                    ),
                ),
                $html->form(false)->action('admin/product-edit')->method('get')->class('body-cell-action', 'edit-action')->add(
                    $html->input('hidden')->name('public_id')->value($this->presenter->showField($product, 'public_id')),
                    $html->button('submit')->class('icon-container')->add(
                        $this->icon->createIcon($html, 'icon-edit', 'Edit', ['edit']),
                        $html->tag('span')->class('visually-hidden')->content('Edit Product A'),
                    ),
                ),
                $html->form()->action('admin/confirm-product-deletion')->method('post')->class('body-cell-action', 'trash-action')->add(
                    $html->input('hidden')->name('public_id')->value($this->presenter->showField($product, 'public_id')),
                    $html->button('button')->class('icon-container', 'modal-open-btn')->custom(['data-action' => 'open-delete-modal'])->add(
                        $this->icon->createIcon($html, 'icon-trash', 'Delete', ['trash']),
                        $html->tag('span')->class('visually-hidden')->content('Delete Product A'),
                    ),
                ),
            ),
        );
    }
}
