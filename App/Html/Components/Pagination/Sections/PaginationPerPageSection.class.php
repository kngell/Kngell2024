<?php

declare(strict_types=1);

class PaginationPerPageSection implements PaginationSectionInterface
{
    private array $allowedPageSizes;

    public function __construct(
        private HtmlBuilder $builder,
        private int $recordsPerPage,
        array $allowedPageSizes,
        private Request $request,
    ) {
        $this->allowedPageSizes = $allowedPageSizes;
        if (!in_array($this->recordsPerPage, $this->allowedPageSizes, true)) {
            $this->allowedPageSizes[] = $this->recordsPerPage;
            sort($this->allowedPageSizes);
        }
    }

    public function supports(string $key): bool
    {
        return $key === 'perPage';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;
        $queryParams = $this->request->getQuery()->getAll();
        unset($queryParams['per_page'], $queryParams['page']);
        $actionPath = ltrim($this->request->getPathFromUri() ?? '', '/');

        $baseQuery = http_build_query($queryParams);
        $urlStart = empty($baseQuery) ? '?' : '?' . $baseQuery . '&';
        $baseUrlTemplate = $urlStart . 'per_page=';

        return $html->tag('div')->class('pagination__per-page')->add(
            $html->label('Items per page:')->for('per-page-selector')->class('pagination__per-page-label'),
            $html->form()
                ->method('get')
                ->action($actionPath)
                ->id('pagination-form-perpage')
                ->add(
                    $html->input('hidden')->name('page')->value('1'),
                    $html->tag('select')
                        ->id('per-page-selector')
                        ->name('per_page')
                        ->class('pagination__select')

                        ->onchange('this.form.submit()')
                        ->custom(['data-base-url' => $baseUrlTemplate])
                        ->add(
                            ...$this->generateOptions(),
                        ),
                    ...$this->generateHiddenInputs($queryParams),
                ),
            $html->htmlBlock('<script>' . $this->getJsBindingScript() . '</script>'),
        );
    }

    public function getSectionJsOnly(): AbstractHtmlComponent
    {
        $html = $this->builder;
        $queryParams = $this->request->getQuery()->getAll();
        unset($queryParams['per_page'], $queryParams['page']);
        $baseQuery = http_build_query($queryParams);
        $urlStart = empty($baseQuery) ? '?' : '?' . $baseQuery . '&';

        $baseUrlTemplate = $urlStart . 'per_page=';

        return $html->tag('div')->class('pagination__per-page')->add(
            $html->label('Items per page:')->for('per-page')->class('pagination__per-page-label'),
            $html->tag('select')
                ->id('per-page-selector')
                ->class('pagination__select')

                ->custom(['data-base-url' => $baseUrlTemplate])
                ->add(
                    ...$this->generateOptions(),
                ),
            $html->htmlBlock('<script>' . $this->getJsBindingScript() . '</script>'),
        );
    }

    private function getJsBindingScript(): string
    {
        return "
        var selector = document.getElementById('per-page-selector');
        var form = document.getElementById('pagination-form-perpage');

        if (selector && form) {
            // 1. Disable PHP Fallback (Remove the attribute if JS is enabled)
            selector.removeAttribute('onchange'); 
            
            // 2. Enable smooth JS feature
            selector.addEventListener('change', function(e) {
                var baseUrl = selector.getAttribute('data-base-url');
                var newUrl = baseUrl + selector.value + '&page=1';
                window.location.href = newUrl;
            });
        }
    ";
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function generateOptions(): array
    {
        $options = [];
        $html = $this->builder;

        foreach ($this->allowedPageSizes as $size) {
            $options[] = $html->option((string) $size, $size)->selected($this->recordsPerPage === $size);
        }

        return $options;
    }

    /**
     * Helper to generate hidden inputs for all existing query parameters.
     *
     * @param array $queryParams
     *
     * @return AbstractHtmlComponent[]
     */
    private function generateHiddenInputs(array $queryParams): array
    {
        $inputs = [];
        $html = $this->builder;

        foreach ($queryParams as $key => $value) {
            if (is_scalar($value)) {
                $inputs[] = $html->input('hidden')
                                 ->name($key)
                                 ->value((string) $value);
            }
        }
        return $inputs;
    }
}