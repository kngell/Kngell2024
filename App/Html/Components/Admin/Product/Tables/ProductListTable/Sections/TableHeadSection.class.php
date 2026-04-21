<?php

declare(strict_types=1);

class TableHeadSection extends AbstractTableSection implements ProductTableSectionInterface
{
    private const string TABLE_HEAD_TEMPLATE_PATH = APP . DS . 'Views' . DS . 'Backend/admin/partials/products/productTableHead.php';

    public function __construct(HtmlBuilder $builder, IconBuilder $icon, private FileContentManager $file, HtmlSectionPresentationService $presenter)
    {
        parent::__construct($builder, $icon, $presenter);
    }

    public function supports(string $key): bool
    {
        return $key === 'thead';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $icon = $this->icon->createIcon($this->builder, 'icon-arrow-down', 'Arrow Down', ['arrow-down']);
        $tableHead = $this->file->requirePhp(self::TABLE_HEAD_TEMPLATE_PATH, $icon->generate());
        $builder = $this->builder;
        return $builder->htmlBlock($tableHead);
    }

    public function tableHead(): AbstractHtmlComponent
    {
        $html = $this->builder;

        return $html->tag('thead')->class('table__head')->add(
            $html->tag('tr')->class('table__head--row')->add(
                $html->tag('th')->custom(['scope' => 'col'])->class('table__head--row-cell')->add(
                    $html->tag('div')->class('header-cell')->add(
                        $html->tag('div')->class('header-cell__top-row')->add(
                            $this->headerCellTopRowCheckbox(),
                            $this->headerCellTopRowDropdown(),
                        ),
                        $html->tag('span')->class('header-cell__hint-text'),
                    ),
                ),
                $html->tag('th')->custom(['scope' => 'col'])->class('table__head--row-cell')->add(
                    $html->tag('span')->class('header-cell'),
                ),
            ),
        );
    }

    private function headerCellTopRowCheckbox(): AbstractHtmlComponent
    {
        $html = $this->builder;
        return  $html->tag('div')->class('checkbox-box')->add(
            $html->tag('span')->id('select-all-label')->class('"visually-hidden"')->content('Select all products'),
            $html->input('checkbox')->id('select-all')->class('checkbox-box__input')->custom(['aria-labelledby' => 'select-all-label']),
            $html->label('products')->for('select-all')->class('checkbox-box__label'),
        );
    }

    private function headerCellTopRowDropdown(): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('div')->class('dropdown-container')->add(
            $html->button('button')->class('dropdown-container__btn')->custom(['aria-expanded' => 'false',
                'aria-controls' => 'advanced-selection',
            ])->add(
                $this->icon->createIcon($html, 'icon-arrow-down', 'dropdown', ['arrow-down']),
            ),
            $html->tag('div')->id('advanced-selection')->class('dropdown-container__dropdown')->hidden(true),
        );
    }
}