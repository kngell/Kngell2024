<?php

declare(strict_types=1);

class AdminSearchAndFilter implements StandAloneComponentInterface
{
    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly IconBuilder $iconBuilder,
    ) {
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        return $this->htmlBuilder->div()
             ->class('admin-search-filter')
             ->add(
                 $this->buildSearchInput(),
                 $this->buildFilters(),
             );
    }

    private function buildSearchInput(): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        // Create form
        $form = $html->form()
            ->name('search-form')
            ->method('post')
            ->action('')
            ->class('search-form');

        // Add search input
        $input = $html->input('text')
            ->name('search')
            ->id('search-form--input-id')
            ->placeholder('Search product...')
            ->class('search-form__input');

        $button = $html->button()->class('search-form__btn')->add(
            $this->iconBuilder->createIcon('icon-search', 'Search', ['search']),
        );

        return $form->add(
            $button,
            $input,
        );
    }

    private function buildFilters(): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $buttonCalendar = $html->button()->class('right__date-picker')->add(
            $html->tag('span')->class('icon-container')->add(
                $this->iconBuilder->createIcon('icon-calendar', 'Calendar', ['calendar']),
            ),
            $html->tag('span')->class('icon-text')->content('Select Dates'),
        );

        $buttonFilter = $html->button()->class('right__filter')->add(
            $html->tag('span')->class('icon-container')->add(
                $this->iconBuilder->createIcon('icon-slider', 'Slider', ['slider']),
            ),
            $html->tag('span')->class('icon-text')->content('Filters'),
        );

        return $html->div()->class('right')->add(
            $buttonCalendar,
            $buttonFilter,
        );
    }
}