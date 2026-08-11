<?php

declare(strict_types=1);

/**
 * @deprecated The filter buttons here use legacy markup (.right__date-picker,
 * .icon-container) that predates the standardized ButtonBuilder design.
 * This class will be deleted once those buttons are redesigned to use .btn /
 * .btn__icon / .btn__label, at which point the search row will be folded into
 * AdminMainHeader via AdminHeaderConfig.
 */
class AdminSearchAndFilter implements StandAloneComponentInterface
{
    private string $searchPlaceholder = 'Search...';

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly IconBuilder $iconBuilder,
    ) {
    }

    public function withSearchPlaceholder(string $placeholder): self
    {
        $this->searchPlaceholder = $placeholder;
        return $this;
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

        $form = $html->form()
            ->name('search-form')
            ->method('post')
            ->action('')
            ->class('search-form');

        $input = $html->input('text')
            ->name('search')
            ->id('search-form--input-id')
            ->placeholder($this->searchPlaceholder)
            ->class('search-form__input');

        $button = $html->button()->class('search-form__btn')->add(
            $this->iconBuilder->createIcon('icon-search', 'Search', ['search']),
        );

        return $form->add($button, $input);
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

        return $html->div()->class('right')->add($buttonCalendar, $buttonFilter);
    }
}