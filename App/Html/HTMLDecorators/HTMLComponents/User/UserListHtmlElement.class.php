<?php

declare(strict_types=1);

class UserListHtmlElement extends AbstractHtml
{
    private array $userInfos;
    private array $userData;
    private HtmlBuilder $builder;

    public function __construct(array $userData, HtmlBuilder $builder, array $userInfos)
    {
        $this->userInfos = $userInfos;
        $this->userData = $userData;
        $this->builder = $builder;
    }

    public function display(): string
    {
        $html = $this->builder;
        return $html->tag('div')->class('table-container')->add(
            $html->tag('div')->class('table-container--row')->add(
                $this->tableHeadSection($html),
                $html->tag('section')->class('table-container__body')->add(
                    $html->tag('table')->class('table')->add(
                        $html->tag('thead')->class('table__head')->add(
                            $html->tag('tr')->class('table-row', 'table__head--row')->add(
                                ...$this->tableHeader($html)
                            )
                        ),
                        $html->tag('tbody')->class('table__body')->add(
                            ...$this->tableBody($html),
                        )
                    )
                )
            )
        )->generate();
    }

    private function tableHeadSection(HtmlBuilder $html) : AbstractHtmlComponent
    {
        return   $html->tag('section')->class('table-container__header')->add(
            $html->tag('h1')->content('User List'),
            $html->tag('div')->class('search-box')->add(
                $html->input('search')->name('search')->id('search')->placeholder('Search Data...')->class('search-box__search-input'),
                $html->tag('span')->class('search-box__icon-container')->add(
                    $html->tag('i')->class('fa-solid fa-magnifying-glass')
                )
            ),
            $html->tag('div')->class('export-box')->add(
                $html->label()->for('export-box__files')->class('export-box__files')->title('export-files'),
                $html->input('checkbox')->name('export-files')->id('export-box__files')->class('export-box__input-checkbox'),
                $html->tag('div')->class('export-box__options')->add(
                    $html->label('Export as&nbsp;&#10140;'),
                    $html->label('PDF')->for('export-box__files')->id('exportPdf')->add(
                        $html->tag('img')->src('/public/assets/img/pdf.png')->alt('PDF')->class('export-box__options--pdf')
                    ),
                    $html->label('JSON')->for('export-box__files')->id('exportJson')->add(
                        $html->tag('img')->src('/public/assets/img/json.png')->alt('JSON')->class('export-box__options--json')
                    ),
                    $html->label('CSV')->for('export-box__files')->id('exportCsv')->add(
                        $html->tag('img')->src('/public/assets/img/csv.png')->alt('CSV')->class('export-box__options--csv')
                    ),
                    $html->label('Excel')->for('export-box__files')->id('exportXls')->add(
                        $html->tag('img')->src('/public/assets/img/excel.png')->alt('Excel')->class('export-box__options--xls')
                    ),
                )
            )
        );
    }

    private function tableHeader(HtmlBuilder $html): array
    {
        $tableHeads = [];
        foreach ($this->userInfos as $field) {
            $content = StringUtils::camelCaseToSnakeCase($field);
            $content = str_replace('_', ' ', $content);
            $tableHeads[] = $html->tag('th')->class('table-heading')->content(ucfirst($content))->add(
                $html->tag('span')->content('&UpArrow;')->class('table-heading__icon')
            );
        }
        return $tableHeads;
    }

    private function tableBody(HtmlBuilder $html): array
    {
        $tableBody = [];
        /** @var User $user */
        foreach ($this->userData as $user) {
            $tableBody[] = $html->tag('tr')->class('table-row', 'table__body--row')->add(
                ...$this->tableRow($html, $user)
            );
        }
        return $tableBody;
    }

    private function tableRow(HtmlBuilder $html, User $user) : array
    {
        $tableRow = [];
        foreach ($this->userInfos as $field) {
            if ($field === 'media') {
                $media = $this->media(strval($user->getFieldValue($field)));
                $tableRow[] = $html->tag('td')->class('table-data', 'table__body--row-data', 'name')->add(
                    $this->mediaImage($user, $media, $field, $html)
                );
            } elseif ($field === 'action') {
                $tableRow[] = $html->tag('td')->class('table-data', 'table__body--row-data')->add(
                    $html->tag('div')->class('table__body--row-data', 'action')->add(
                        $html->tag('a')->href('/profile/edit/' . $user->getUserId())->class('table__body--row-data--edit')->content('Edit')
                    )
                );
            } else {
                $tableRow[] = $html->tag('td')->class('table-data', 'table__body--row-data')->content(strval($user->getFieldValue($field)));
            }
        }
        return $tableRow;
    }

    private function mediaImage(User $user, string|null $media, string $field, HtmlBuilder $html) : AbstractHtmlComponent
    {
        if (! empty($media)) {
            return $html->tag('div')->class('name__img-container')->add(
                $html->tag('img')->src($this->media(strval($user->getFieldValue($field))))->class('name__img-container--img')->alt('Customer Image'),
                $html->tag('p')->class('name__img-container--text')->content($user->getFirstName() . ' ' . $user->getLastName())
            );
        } else {
            return $html->tag('div')->class('name__img-container')->add();
        }
    }
}